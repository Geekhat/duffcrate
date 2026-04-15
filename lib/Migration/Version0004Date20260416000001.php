<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Creates the crate_shares table for album/playlist sharing between users.
 */
class Version0004Date20260416000001 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('crate_shares')) {
            $table = $schema->createTable('crate_shares');

            $table->addColumn('id', Types::INTEGER, [
                'autoincrement' => true,
                'notnull'       => true,
            ]);
            // User who owns / created the share
            $table->addColumn('owner_user_id', Types::STRING, [
                'notnull' => true,
                'length'  => 64,
            ]);
            // User the item is shared with
            $table->addColumn('shared_with_user_id', Types::STRING, [
                'notnull' => true,
                'length'  => 64,
            ]);
            // 'album' or 'playlist'
            $table->addColumn('shareable_type', Types::STRING, [
                'notnull' => true,
                'length'  => 16,
            ]);
            $table->addColumn('shareable_id', Types::INTEGER, [
                'notnull' => true,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_user_id'], 'crate_share_owner');
            $table->addIndex(['shared_with_user_id'], 'crate_share_recipient');
            $table->addUniqueIndex(
                ['owner_user_id', 'shared_with_user_id', 'shareable_type', 'shareable_id'],
                'crate_share_unique',
            );
        }

        return $schema;
    }
}
