<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Creates crate_playlists and crate_playlist_items tables.
 */
class Version0003Date20260416000000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // ── Playlists ──────────────────────────────────────────────────────────
        if (!$schema->hasTable('crate_playlists')) {
            $table = $schema->createTable('crate_playlists');

            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull'       => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length'  => 64,
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length'  => 500,
            ]);
            $table->addColumn('description', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('updated_at', Types::DATETIME, [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'crate_playlist_user_id');
        }

        // ── Playlist items ─────────────────────────────────────────────────────
        if (!$schema->hasTable('crate_playlist_items')) {
            $table = $schema->createTable('crate_playlist_items');

            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull'       => true,
            ]);
            $table->addColumn('playlist_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('media_item_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('position', Types::INTEGER, [
                'notnull' => true,
                'default' => 0,
            ]);
            $table->addColumn('added_at', Types::DATETIME, [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['playlist_id'], 'crate_pli_playlist_id');
            $table->addUniqueIndex(['playlist_id', 'media_item_id'], 'crate_pli_unique');
        }

        return $schema;
    }
}
