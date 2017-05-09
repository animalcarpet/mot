<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * AuthForAeStatus.
 *
 * @ORM\Table(name="site_contact_type")
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\SiteContactTypeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class SiteContactType extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;
}
