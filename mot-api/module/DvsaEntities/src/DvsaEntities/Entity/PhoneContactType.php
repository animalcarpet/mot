<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * Phone contact type.
 *
 * @ORM\Table(
 *   name = "phone_contact_type",
 *   uniqueConstraints = {
 *      @ORM\UniqueConstraint(name = "uk_phone_contact_type_code", columns = {"code"})
 *   }
 * )
 * @ORM\Entity(repositoryClass = "DvsaEntities\Repository\PhoneContactTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 *
 * @see DvsaCommon\Enum\PhoneContactTypeCode supported code values
 */
class PhoneContactType
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
     * @param string $name
     *
     * @return PhoneContactType
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
