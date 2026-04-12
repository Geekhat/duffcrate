<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0001Date20260412000000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('crate_media_items')) {
            $table = $schema->createTable('crate_media_items');

            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('title', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('artist', Types::STRING, [
                'notnull' => true,
                'length' => 500,
            ]);
            $table->addColumn('format', Types::STRING, [
                'notnull' => true,
                'length' => 50,
            ]);
            $table->addColumn('year', Types::INTEGER, [
                'notnull' => false,
            ]);
            $table->addColumn('barcode', Types::STRING, [
                'notnull' => false,
                'length' => 50,
            ]);
            $table->addColumn('notes', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->addColumn('status', Types::STRING, [
                'notnull' => true,
                'length' => 10,
                'default' => 'owned',
            ]);
            $table->addColumn('discogs_id', Types::STRING, [
                'notnull' => false,
                'length' => 50,
            ]);
            $table->addColumn('artwork_path', Types::STRING, [
                'notnull' => false,
                'length' => 1000,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('updated_at', Types::DATETIME, [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'crate_media_user_id');
            $table->addIndex(['user_id', 'format'], 'crate_media_user_format');
            $table->addIndex(['user_id', 'status'], 'crate_media_user_status');
        }

        return $schema;
    }
}
