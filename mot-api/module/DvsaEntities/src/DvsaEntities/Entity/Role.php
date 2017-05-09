<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Role.
 *
 * @ORM\Table(
 *  name="role",
 *  indexes={
 *      @ORM\Index(name="created_by", columns={"created_by"}),
 *      @ORM\Index(name="last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\RoleRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class Role extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_internal", type="boolean")
     */
    private $isInternal;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_trade", type="boolean")
     */
    private $isTrade;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Role
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param bool $isInternal
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsInternal($isInternal)
    {
        $this->isInternal = $isInternal;

        return $this;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function isInternal()
    {
        return $this->isInternal;
    }

    /**
     * @param bool $isTrade
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsTrade($isTrade)
    {
        $this->isTrade = $isTrade;

        return $this;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function isTrade()
    {
        return $this->isTrade;
    }
}
