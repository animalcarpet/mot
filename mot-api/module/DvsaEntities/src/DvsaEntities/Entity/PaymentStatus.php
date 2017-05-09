<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * Payment status.
 *
 * @ORM\Table(name="payment_status")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class PaymentStatus
{
    use CommonIdentityTrait;

    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=10, nullable=false)
     */
    private $name;

    /**
     * @var int CPMS code for given status type
     *
     * @ORM\Column(name="cpms_code", type="integer", nullable=false)
     */
    private $cpmsCode;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCpmsCode()
    {
        return $this->cpmsCode;
    }

    /**
     * @param int $cpmsCode
     *
     * @return $this
     */
    public function setCpmsCode($cpmsCode)
    {
        $this->cpmsCode = $cpmsCode;

        return $this;
    }
}
