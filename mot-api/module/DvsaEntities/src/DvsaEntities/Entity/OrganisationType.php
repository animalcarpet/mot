<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * OrganisationType.
 *
 * @ORM\Table(name="organisation_type", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\OrganisationTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class OrganisationType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    const ENTITY_NAME = 'Organisation Type';

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
