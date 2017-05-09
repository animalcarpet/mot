<?php

namespace PersonApi\Dto;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\Entity\AuthorisationForTestingMot;

/**
 * Represents personal authorisations for testing mot.
 */
class MotTestingAuthorisationCollector
{
    const PREFIX = 'class';

    /** @var $authorisedForClass array */
    private $authorisedForClass;

    /**
     * @param AuthorisationForTestingMot[] $authorisationList
     */
    public function __construct($authorisationList)
    {
        $this->authorisedForClass = [
            self::PREFIX.VehicleClassCode::CLASS_1 => null,
            self::PREFIX.VehicleClassCode::CLASS_2 => null,
            self::PREFIX.VehicleClassCode::CLASS_3 => null,
            self::PREFIX.VehicleClassCode::CLASS_4 => null,
            self::PREFIX.VehicleClassCode::CLASS_5 => null,
            self::PREFIX.VehicleClassCode::CLASS_7 => null,
        ];

        /** @var $vcAuth AuthorisationForTestingMot */
        foreach ($authorisationList as $vcAuth) {
            $key = self::PREFIX.$vcAuth->getVehicleClass()->getCode();
            $this->authorisedForClass[$key] = $vcAuth->getStatus()->getCode();
        }
    }

    public function toArray()
    {
        return $this->authorisedForClass;
    }
}
