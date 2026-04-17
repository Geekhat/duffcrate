<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Adds pre-enrichment snapshot columns to crate_media_items so that
 * "Remove Discogs data" can restore the original manual values.
 */
class Version0005Date20260417000000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        $table = $schema->getTable('crate_media_items');

        if (!$table->hasColumn('original_title')) {
            $table->addColumn('original_title', Types::STRING, [
                'notnull' => false,
                'length'  => 500,
            ]);
        }
        if (!$table->hasColumn('original_artist')) {
            $table->addColumn('original_artist', Types::STRING, [
                'notnull' => false,
                'length'  => 500,
            ]);
        }
        if (!$table->hasColumn('original_year')) {
            $table->addColumn('original_year', Types::INTEGER, [
                'notnull' => false,
            ]);
        }
        if (!$table->hasColumn('original_artwork_path')) {
            $table->addColumn('original_artwork_path', Types::STRING, [
                'notnull' => false,
                'length'  => 1000,
            ]);
        }

        return $schema;
    }
}
