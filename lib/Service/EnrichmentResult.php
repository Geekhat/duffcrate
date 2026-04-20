<?php

declare(strict_types=1);

namespace OCA\Crate\Service;

use OCA\Crate\Db\MediaItem;

/**
 * Simple result object returned by EnrichmentService — either the updated
 * MediaItem or an (error message, HTTP status) pair. Keeps the service free
 * of DataResponse coupling so the controller owns the HTTP shape.
 */
final class EnrichmentResult
{
    private function __construct(
        public readonly ?MediaItem $item,
        public readonly ?string $error,
        public readonly int $status,
    ) {
    }

    public static function ok(MediaItem $item): self
    {
        return new self($item, null, 200);
    }

    public static function error(string $message, int $status): self
    {
        return new self(null, $message, $status);
    }

    public function isOk(): bool
    {
        return $this->item !== null;
    }
}
