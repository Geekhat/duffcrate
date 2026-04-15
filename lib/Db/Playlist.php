<?php

declare(strict_types=1);

namespace OCA\Crate\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getName()
 * @method void setName(string $name)
 * @method string|null getDescription()
 * @method void setDescription(?string $description)
 * @method string|null getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string|null getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Playlist extends Entity implements \JsonSerializable
{
    protected string $userId = '';
    protected string $name = '';
    protected ?string $description = null;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'createdAt'   => $this->createdAt,
            'updatedAt'   => $this->updatedAt,
        ];
    }
}
