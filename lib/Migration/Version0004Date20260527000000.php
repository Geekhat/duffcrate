<?php

declare(strict_types=1);

namespace OCA\Crate\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Adds purchase-price fields so the catalogue can track what the user paid
 * for an item alongside its current market value. Note that this is NOT
 * reusing the `original_*` column family — those are pre-enrichment
 * snapshots used by the "Remove enrichment data" action, an unrelated
 * concept.
 *
 * Column types mirror `market_value` + `market_value_currency` so the two
 * money fields behave consistently end-to-end.
 */
class Version0004Date20260527000000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('crate_media_items')) {
            return $schema;
        }

        $media = $schema->getTable('crate_media_items');

        if (!$media->hasColumn('purchase_price')) {
            $media->addColumn('purchase_price', Types::FLOAT, ['notnull' => false]);
        }
        if (!$media->hasColumn('purchase_price_currency')) {
            $media->addColumn('purchase_price_currency', Types::STRING, ['notnull' => false, 'length' => 3]);
        }

        return $schema;
    }
}
