<?php

declare(strict_types=1);

namespace OCA\Crate\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getOwnerUserId()
 * @method void setOwnerUserId(string $ownerUserId)
 * @method string getSharedWithUserId()
 * @method void setSharedWithUserId(string $sharedWithUserId)
 * @method string getShareableType()
 * @method void setShareableType(string $shareableType)
 * @method int getShareableId()
 * @method void setShareableId(int $shareableId)
 * @method string|null getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 */
class CrateShare extends Entity implements \JsonSerializable
{
    protected string $ownerUserId = '';
    protected string $sharedWithUserId = '';
    protected string $shareableType = '';
    protected int $shareableId = 0;
    protected ?string $createdAt = null;

    public function __construct()
    {
        $this->addType('shareableId', 'integer');
    }

    public function jsonSerialize(): array
    {
        return [
            'id'               => $this->id,
            'ownerUserId'      => $this->ownerUserId,
            'sharedWithUserId' => $this->sharedWithUserId,
            'shareableType'    => $this->shareableType,
            'shareableId'      => $this->shareableId,
            'createdAt'        => $this->createdAt,
        ];
    }
}
