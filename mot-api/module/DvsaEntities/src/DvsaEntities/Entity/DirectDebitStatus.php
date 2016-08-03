<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * DirectDebitStatus
 *
 * @ORM\Table(name="direct_debit_status")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\DirectDebitStatusRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class DirectDebitStatus extends Entity
{
    use CommonIdentityTrait;

    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string CPMS mandate status code
     *
     * @ORM\Column(name="cpms_code", type="string", length=5, nullable=false)
     */
    private $cpmsCode;

    /**
     * @return string
     */
    public function getCpmsCode()
    {
        return $this->cpmsCode;
    }

    /**
     * @param string $cpmsCode
     *
     * @return $this
     */
    public function setCpmsCode($cpmsCode)
    {
        $this->cpmsCode = $cpmsCode;

        return $this;
    }

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
}
