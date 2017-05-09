<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * TransmissionType.
 *
 * @ORM\Table(
 *  name="transmission_type",
 *  indexes={
 *      @ORM\Index(name="fk_transmission_type_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_transmission_type_last_updated_by", columns={"last_updated_by"})
 *  })
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\TransmissionTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class TransmissionType extends Entity
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
