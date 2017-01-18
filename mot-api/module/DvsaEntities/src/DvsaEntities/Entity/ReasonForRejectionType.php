<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ReasonForRejectionType
 *
 * @ORM\Table(name="reason_for_rejection_type")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class ReasonForRejectionType
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $reasonForRejectionType;

    /**
     * Get reasonForRejectionType
     *
     * @return string
     */
    public function getReasonForRejectionType()
    {
        return $this->reasonForRejectionType;
    }

    /**
     * @param string $reasonForRejectionType
     * @return ReasonForRejectionType
     */
    public function setReasonForRejectionType($reasonForRejectionType)
    {
        $this->reasonForRejectionType = $reasonForRejectionType;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getReasonForRejectionType();
    }
}
