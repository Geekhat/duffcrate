<?php

declare(strict_types=1);

namespace OCA\Crate\Service;

use OCA\Crate\Db\MediaItem;
use OCA\Crate\Db\MediaItemMapper;

class MediaService
{
    public function __construct(private readonly MediaItemMapper $mapper)
    {
    }

    /** @return MediaItem[] */
    public function findAll(string $userId): array
    {
        return $this->mapper->findAll($userId);
    }

    public function find(int $id, string $userId): MediaItem
    {
        return $this->mapper->findByUser($id, $userId);
    }

    public function create(
        string $userId,
        string $title,
        string $artist,
        string $format,
        ?int $year,
        ?string $barcode,
        ?string $notes,
        string $status,
    ): MediaItem {
        $item = new MediaItem();
        $item->setUserId($userId);
        $item->setTitle($title);
        $item->setArtist($artist);
        $item->setFormat($format);
        $item->setYear($year);
        $item->setBarcode($barcode);
        $item->setNotes($notes);
        $item->setStatus($status);
        $now = new \DateTime();
        $item->setCreatedAt($now);
        $item->setUpdatedAt($now);
        return $this->mapper->insert($item);
    }

    public function update(
        int $id,
        string $userId,
        string $title,
        string $artist,
        string $format,
        ?int $year,
        ?string $barcode,
        ?string $notes,
        string $status,
    ): MediaItem {
        $item = $this->mapper->findByUser($id, $userId);
        $item->setTitle($title);
        $item->setArtist($artist);
        $item->setFormat($format);
        $item->setYear($year);
        $item->setBarcode($barcode);
        $item->setNotes($notes);
        $item->setStatus($status);
        $item->setUpdatedAt(new \DateTime());
        return $this->mapper->update($item);
    }

    public function delete(int $id, string $userId): void
    {
        $item = $this->mapper->findByUser($id, $userId);
        $this->mapper->delete($item);
    }
}
