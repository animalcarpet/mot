<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * @internal We are not extending to Entity superclass.  This is due to a decision of renaming 'Updated' to 'Modified'
 *
 * @ORM\Table(name="site_status_lookup")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteStatusRepository")
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class SiteStatus
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
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
