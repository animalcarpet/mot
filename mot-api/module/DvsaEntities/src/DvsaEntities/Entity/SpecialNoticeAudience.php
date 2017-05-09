<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Special Notice Audience.
 *
 * @ORM\Table(
 *  name="special_notice_audience",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity
 */
class SpecialNoticeAudience extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\SpecialNoticeContent
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\SpecialNoticeContent", inversedBy="audience")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_notice_content_id", referencedColumnName="id")
     * })
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="special_notice_audience_type_id", type="integer", nullable=false)
     */
    private $audienceId;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_class_id", type="integer", nullable=false)
     */
    private $vehicleClassId;

    /**
     * @param $audienceId
     *
     * @return SpecialNoticeAudience
     */
    public function setAudienceId($audienceId)
    {
        $this->audienceId = $audienceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAudienceId()
    {
        return $this->audienceId;
    }

    /**
     * @param $content
     *
     * @return SpecialNoticeAudience
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\SpecialNoticeContent
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param int $vehicleClassId
     *
     * @return SpecialNoticeAudience
     */
    public function setVehicleClassId($vehicleClassId)
    {
        $this->vehicleClassId = $vehicleClassId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVehicleClassId()
    {
        return $this->vehicleClassId;
    }
}
