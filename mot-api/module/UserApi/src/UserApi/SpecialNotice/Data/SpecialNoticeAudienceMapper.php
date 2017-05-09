<?php

namespace UserApi\SpecialNotice\Data;

use DvsaCommon\Constants\SpecialNoticeAudience;
use DvsaCommon\Enum\SpecialNoticeAudienceTypeId;
use DvsaCommon\Enum\VehicleClassId;

/**
 * SpecialNoticeAudienceMapper.
 */
class SpecialNoticeAudienceMapper
{
    public static $stringToObject;

    private $audienceId;
    private $vehicleClassId;

    public function __construct($audienceId, $vehicleClassId)
    {
        $this->audienceId = $audienceId;
        $this->vehicleClassId = $vehicleClassId;
    }

    public function getAudienceId()
    {
        return $this->audienceId;
    }

    public function getVehicleClassId()
    {
        return $this->vehicleClassId;
    }

    public static function mapToObject($audienceString)
    {
        if (self::hasAudience($audienceString)) {
            return self::$stringToObject[$audienceString];
        }

        throw new \InvalidArgumentException('Invalid audience : '.$audienceString);
    }

    /**
     * @param SpecialNoticeAudienceMapper $audienceVehicleClass
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function mapToString(SpecialNoticeAudienceMapper $audienceVehicleClass)
    {
        foreach (self::$stringToObject as $audienceString => $audience) {
            if ($audienceVehicleClass->equals($audience)) {
                return strval($audienceString);
            }
        }

        throw new \InvalidArgumentException(
            'Invalid audience with audienceId: '.
            $audienceVehicleClass->getAudienceId().
            ' and vehicleClassId: '.
            $audienceVehicleClass->getVehicleClassId()
        );
    }

    public static function hasAudience($audienceString)
    {
        return array_key_exists($audienceString, self::$stringToObject);
    }

    public function equals(SpecialNoticeAudienceMapper $other)
    {
        return $other->getAudienceId() === $this->getAudienceId()
        && $other->getVehicleClassId() === $this->getVehicleClassId();
    }
}

SpecialNoticeAudienceMapper::$stringToObject = [
    SpecialNoticeAudience::DVSA => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::DVSA_AUDIENCE,
            null
        ),
    SpecialNoticeAudience::VTS => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::VTS_AUDIENCE,
            null
        ),
    SpecialNoticeAudience::TESTER_CLASS_1 => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::TESTER_AUDIENCE,
            VehicleClassId::CLASS_1
        ),
    SpecialNoticeAudience::TESTER_CLASS_2 => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::TESTER_AUDIENCE,
            VehicleClassId::CLASS_2
        ),
    SpecialNoticeAudience::TESTER_CLASS_3 => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::TESTER_AUDIENCE,
            VehicleClassId::CLASS_3
        ),
    SpecialNoticeAudience::TESTER_CLASS_4 => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::TESTER_AUDIENCE,
            VehicleClassId::CLASS_4
        ),
    SpecialNoticeAudience::TESTER_CLASS_5 => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::TESTER_AUDIENCE,
            VehicleClassId::CLASS_5
        ),
    SpecialNoticeAudience::TESTER_CLASS_7 => new SpecialNoticeAudienceMapper(
            SpecialNoticeAudienceTypeId::TESTER_AUDIENCE,
            VehicleClassId::CLASS_7
        ),
];
