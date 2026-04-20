<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Tightens data integrity on the schema added by Versions 0003 / 0007:
 *   - category must be NOT NULL with a default of 'music' (matches MediaItem::$category)
 *   - FK from crate_playlist_items.playlist_id   → crate_playlists.id     ON DELETE CASCADE
 *   - FK from crate_playlist_items.media_item_id → crate_media_items.id   ON DELETE CASCADE
 *
 * crate_shares remains polymorphic (shareable_type + shareable_id) so cannot carry
 * a DB-level FK; orphan cleanup there is handled in MediaService / PlaylistService
 * transactionally.
 */
class Version0009Date20260420000001 extends SimpleMigrationStep
{
    public function __construct(
        private readonly IDBConnection $db,
    ) {
    }

    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
    {
        // Backfill any remaining NULL categories so the NOT NULL alteration
        // below succeeds. Belt-and-braces against 0007 having been partially
        // applied on any install.
        $qb = $this->db->getQueryBuilder();
        $qb->update('crate_media_items')
            ->set('category', $qb->createNamedParameter('music'))
            ->where($qb->expr()->isNull('category'));
        $qb->executeStatement();

        // Purge any orphan playlist_items before adding FKs — dangling rows
        // would cause the constraint creation to fail on legacy installs.
        // The Nextcloud `*PREFIX*` placeholder is expanded to the configured
        // DB table prefix at execute time.
        $this->db->executeStatement(
            'DELETE FROM `*PREFIX*crate_playlist_items`'
            . ' WHERE playlist_id NOT IN (SELECT id FROM `*PREFIX*crate_playlists`)'
        );
        $this->db->executeStatement(
            'DELETE FROM `*PREFIX*crate_playlist_items`'
            . ' WHERE media_item_id NOT IN (SELECT id FROM `*PREFIX*crate_media_items`)'
        );
    }

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // ── Tighten category to NOT NULL with a default ─────────────────────
        $media = $schema->getTable('crate_media_items');
        if ($media->hasColumn('category')) {
            $col = $media->getColumn('category');
            $col->setNotnull(true);
            $col->setDefault('music');
        }

        // ── Add FK constraints on crate_playlist_items ──────────────────────
        $playlistItems = $schema->getTable('crate_playlist_items');
        $playlists     = $schema->getTable('crate_playlists');
        $mediaItems    = $schema->getTable('crate_media_items');

        // FK: playlist_id → playlists.id  (cascade delete)
        if (!$this->hasForeignKeyFor($playlistItems, ['playlist_id'])) {
            $playlistItems->addForeignKeyConstraint(
                $playlists,
                ['playlist_id'],
                ['id'],
                ['onDelete' => 'CASCADE'],
                'crate_pli_fk_playlist',
            );
        }

        // FK: media_item_id → media_items.id  (cascade delete)
        if (!$this->hasForeignKeyFor($playlistItems, ['media_item_id'])) {
            $playlistItems->addForeignKeyConstraint(
                $mediaItems,
                ['media_item_id'],
                ['id'],
                ['onDelete' => 'CASCADE'],
                'crate_pli_fk_media_item',
            );
        }

        // Index on media_item_id helps the FK check and the
        // deleteByMediaItem() query path.
        if (!$playlistItems->hasIndex('crate_pli_media_item_id')) {
            $playlistItems->addIndex(['media_item_id'], 'crate_pli_media_item_id');
        }

        return $schema;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Table $table
     * @param string[]                    $columns
     */
    private function hasForeignKeyFor(\Doctrine\DBAL\Schema\Table $table, array $columns): bool
    {
        foreach ($table->getForeignKeys() as $fk) {
            if ($fk->getLocalColumns() === $columns) {
                return true;
            }
        }
        return false;
    }
}
