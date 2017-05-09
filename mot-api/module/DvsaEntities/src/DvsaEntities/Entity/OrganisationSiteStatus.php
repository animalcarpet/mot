<?php

namespace DvsaEntities\Entity;

use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * OrganisationSiteStatus.
 *
 * @ORM\Table(
 *  name="organisation_site_status",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 *  indexes={
 *      @ORM\Index(name="uk_organisation_site_status_code", columns={"code"}),
 *      @ORM\Index(name="uk_organisation_site_status_name", columns={"name"}),
 *      @ORM\Index(name="fk_organisation_site_map_person_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_organisation_site_map_person_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(
 *  repositoryClass="\DvsaEntities\Repository\OrganisationSiteStatusRepository",
 *  readOnly=true
 * )
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class OrganisationSiteStatus extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    const ENTITY_NAME = 'OrganisationSiteStatus';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }
}
