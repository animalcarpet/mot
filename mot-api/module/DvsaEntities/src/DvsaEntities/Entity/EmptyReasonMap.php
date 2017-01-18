<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Table(name="empty_reason_map")
 * @ORM\Entity
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 *
 */
class EmptyReasonMap extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Vehicle
     *
     * @ORM\OneToOne(targetEntity="Vehicle", mappedBy="id")
     * @ORM\JoinColumn(name="vehicle_id", referencedColumnName="id")
     */
    private $vehicleId;

    /**
     * @var EmptyVinReason
     *
     * @ORM\OneToOne(targetEntity="EmptyVinReason")
     * @ORM\JoinColumn(name="empty_vin_reason_lookup_id", referencedColumnName="id")
     */
    private $emptyVinReason;

    /**
     * @var EmptyVrmReason
     *
     * @ORM\OneToOne(targetEntity="EmptyVinReason")
     * @ORM\JoinColumn(name="empty_vrm_reason_lookup_id", referencedColumnName="id")
     */
    private $emptyVrmReason;

    /**
     * @return Vehicle
     */
    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    /**
     * @param Vehicle $vehicleId
     * @return EmptyReasonMap
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;
        return $this;
    }

    /**
     * @return EmptyVinReason
     */
    public function getEmptyVinReason()
    {
        return $this->emptyVinReason;
    }

    /**
     * @param EmptyVinReason $emptyVinReasonLookupId
     * @return EmptyReasonMap
     */
    public function setEmptyVinReason($emptyVinReasonLookupId)
    {
        $this->emptyVinReason = $emptyVinReasonLookupId;
        return $this;
    }

    /**
     * @return EmptyVrmReason
     */
    public function getEmptyVrmReason()
    {
        return $this->emptyVrmReason;
    }

    /**
     * @param EmptyVrmReason $emptyVrmReason
     * @return EmptyReasonMap
     */
    public function setEmptyVrmReason($emptyVrmReason)
    {
        $this->emptyVrmReason = $emptyVrmReason;
        return $this;
    }
}
