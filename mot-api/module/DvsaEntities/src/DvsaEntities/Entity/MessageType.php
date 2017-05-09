<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * @ORM\Table(name="message_type")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MessageTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class MessageType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="expiry_period", type="integer")
     */
    private $expiryPeriod;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPasswordResetByLetter()
    {
        return $this->code === MessageTypeCode::PASSWORD_RESET_BY_LETTER;
    }

    /**
     * @return bool
     */
    public function isAccountResetByLetter()
    {
        return $this->code === MessageTypeCode::ACCOUNT_RESET_BY_LETTER;
    }
}
