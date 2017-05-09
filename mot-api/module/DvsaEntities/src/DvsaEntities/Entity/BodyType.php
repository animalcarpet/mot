<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * BodyType.
 *
 * @ORM\Table(name="body_type")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\BodyTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 *
 * TODO should be EnumType1 (now is mixing id with code)
 */
class BodyType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

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
