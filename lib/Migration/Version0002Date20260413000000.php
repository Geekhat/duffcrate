<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Adds extended release-detail and artist-profile columns to crate_media_items.
 *
 * New columns
 * -----------
 * label           VARCHAR(500)  – primary label(s) from search result
 * country         VARCHAR(100)  – country of release
 * genres          VARCHAR(500)  – comma-separated genres/styles from /releases/{id}
 * tracklist       TEXT          – JSON array: [{position, title, duration}, …]
 * pressing_notes  TEXT          – release notes from Discogs
 * discogs_artist_id VARCHAR(50) – first artist's Discogs numeric ID
 * artist_bio      TEXT          – biography from /artists/{id}
 * artist_members  TEXT          – JSON array of current/past member names
 */
class Version0002Date20260413000000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('crate_media_items')) {
            return null;
        }

        $table = $schema->getTable('crate_media_items');

        if (!$table->hasColumn('label')) {
            $table->addColumn('label', Types::STRING, [
                'notnull' => false,
                'length'  => 500,
            ]);
        }

        if (!$table->hasColumn('country')) {
            $table->addColumn('country', Types::STRING, [
                'notnull' => false,
                'length'  => 100,
            ]);
        }

        if (!$table->hasColumn('genres')) {
            $table->addColumn('genres', Types::STRING, [
                'notnull' => false,
                'length'  => 500,
            ]);
        }

        if (!$table->hasColumn('tracklist')) {
            $table->addColumn('tracklist', Types::TEXT, [
                'notnull' => false,
            ]);
        }

        if (!$table->hasColumn('pressing_notes')) {
            $table->addColumn('pressing_notes', Types::TEXT, [
                'notnull' => false,
            ]);
        }

        if (!$table->hasColumn('discogs_artist_id')) {
            $table->addColumn('discogs_artist_id', Types::STRING, [
                'notnull' => false,
                'length'  => 50,
            ]);
        }

        if (!$table->hasColumn('artist_bio')) {
            $table->addColumn('artist_bio', Types::TEXT, [
                'notnull' => false,
            ]);
        }

        if (!$table->hasColumn('artist_members')) {
            $table->addColumn('artist_members', Types::TEXT, [
                'notnull' => false,
            ]);
        }

        return $schema;
    }
}
