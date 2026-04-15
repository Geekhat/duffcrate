<?php

declare(strict_types=1);

namespace OCA\Crate\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getPlaylistId()
 * @method void setPlaylistId(int $playlistId)
 * @method int getMediaItemId()
 * @method void setMediaItemId(int $mediaItemId)
 * @method int getPosition()
 * @method void setPosition(int $position)
 * @method string|null getAddedAt()
 * @method void setAddedAt(string $addedAt)
 */
class PlaylistItem extends Entity
{
    protected int $playlistId = 0;
    protected int $mediaItemId = 0;
    protected int $position = 0;
    protected ?string $addedAt = null;

    public function __construct()
    {
        $this->addType('playlistId', 'integer');
        $this->addType('mediaItemId', 'integer');
        $this->addType('position', 'integer');
    }
}
