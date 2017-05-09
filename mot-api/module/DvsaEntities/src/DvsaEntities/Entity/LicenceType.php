<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * LicenceType.
 *
 * @ORM\Table(name="licence_type")
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\LicenceTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class LicenceType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;
}
