<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * PersonContactType.
 *
 * @ORM\Table(name="person_contact_type")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class PersonContactType
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
     * Set name.
     *
     * @param string $name
     *
     * @return PersonContactType
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
}
