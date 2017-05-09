<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * SiteType.
 *
 * @ORM\Table(name="site_type")})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class SiteType
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * Set type.
     *
     * @param string $name
     *
     * @return SiteType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
