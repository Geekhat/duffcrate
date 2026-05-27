<?php

declare(strict_types=1);

namespace OCA\Crate\Dto;

/**
 * Value object for media item create/update payloads.
 *
 * Replaces the 12-parameter method signatures with a single typed argument.
 */
class MediaItemData
{
    public function __construct(
        public readonly string $title,
        public readonly string $artist,
        public readonly string $format,
        public readonly ?int $year = null,
        public readonly ?string $barcode = null,
        public readonly ?string $notes = null,
        public readonly string $status = 'owned',
        public readonly ?string $discogsId = null,
        public readonly ?string $artworkPath = null,
        public readonly ?string $label = null,
        public readonly ?string $country = null,
        public readonly ?string $category = null,
        /**
         * What the user paid for the item, in their chosen currency. Null
         * leaves the stored value unchanged on update; pass a negative
         * sentinel via the controller to clear the field (see
         * MediaController::clearablePrice).
         */
        public readonly ?float $purchasePrice = null,
        public readonly ?string $purchasePriceCurrency = null,
    ) {
    }
}
