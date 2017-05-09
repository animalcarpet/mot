<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * BrakeTestType.
 *
 * @ORM\Table(name="brake_test_type")
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\BrakeTestTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class BrakeTestType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    /**
     * Do not remove! This value is used in DataCatalogService, but shouldn't be used in application.
     *
     * @var string
     *
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
}
