<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SiteContact.
 *
 * @ORM\Table(name="site_contact_detail_map")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteContactRepository")
 */
class SiteContact extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var ContactDetail
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\ContactDetail", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contact_detail_id", referencedColumnName="id")
     * })
     */
    private $contactDetail;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", fetch="LAZY", inversedBy="contacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * @var SiteContactType
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\SiteContactType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_contact_type_id", referencedColumnName="id")
     * })
     */
    private $type;

    public function __construct(ContactDetail $contactDetail, SiteContactType $type, Site $site)
    {
        $this->setDetails($contactDetail);
        $this->type = $type;
        $this->site = $site;
    }

    public function getDetails()
    {
        return $this->contactDetail;
    }

    public function setDetails(ContactDetail $detail)
    {
        $this->contactDetail = $detail;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }
}
