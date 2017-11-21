<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB;

class GroupBPreEuDirectiveReasonForRejection implements GroupBReasonForRejectionInterface
{
    const BONNET_RETAINING_DEVICE_INSECURE = 8565;
    const BODY_INSECURE_AND_DANGEROUS_TO_OTHER_ROAD_USERS = 8453;
    const BODY_HAS_EXCESSIVE_DISPLACEMENT_WHICH_MAY_LEAD_TO_LOSS_OF_VEHICLE_CONTROL = 8454;
    const FRONT_PASSENGER_DOOR_PILLAR_INSECURE = 933;
    const DRIVERS_DOOR_CATCH_MISSING = 932;
    const ANTI_LOCK_BRAKING_SYSTEM_COMPONENT_MISSING = 1125;
    const ANTI_LOCK_BRAKING_SYSTEM_EXCESSIVELY_DAMAGED = 1574;
    const ANTI_LOCK_BRAKING_SYSTEM_WARNING_LAMP_IS_MISSING = 8290;
    const ANTI_LOCK_BRAKING_SYSTEM_INAPPROPRIATELY_REPAIRED = 2054;
    const ANTI_LOCK_BRAKING_SYSTEM_INAPPROPRIATELY_MODIFIED = 2055;
    const TYRE_HAS_PLY_OR_CORDS_EXPOSED = 8385;

    public function getForClass3(): int
    {
        return self::BONNET_RETAINING_DEVICE_INSECURE;
    }

    public function getForClass3Advisory(): int
    {
        return self::BODY_HAS_EXCESSIVE_DISPLACEMENT_WHICH_MAY_LEAD_TO_LOSS_OF_VEHICLE_CONTROL;
    }

    public function getForClass3Prs(): int
    {
        return self::FRONT_PASSENGER_DOOR_PILLAR_INSECURE;
    }

    public function getForClass3Major(): int
    {

    }

    public function getForClass4(): int
    {
        return self::BODY_INSECURE_AND_DANGEROUS_TO_OTHER_ROAD_USERS;
    }

    public function getForClass4Advisory(): int
    {
        return self::BODY_HAS_EXCESSIVE_DISPLACEMENT_WHICH_MAY_LEAD_TO_LOSS_OF_VEHICLE_CONTROL;
    }

    public function getForClass4Prs(): int
    {
        return self::DRIVERS_DOOR_CATCH_MISSING;
    }

    public function getForClass5(): int
    {
        return self::ANTI_LOCK_BRAKING_SYSTEM_COMPONENT_MISSING;
    }

    public function getForClass5Advisory(): int
    {
        return self::ANTI_LOCK_BRAKING_SYSTEM_EXCESSIVELY_DAMAGED;
    }

    public function getForClass5Prs(): int
    {
        return self::ANTI_LOCK_BRAKING_SYSTEM_WARNING_LAMP_IS_MISSING;
    }

    public function getForClass7(): int
    {
        return self::ANTI_LOCK_BRAKING_SYSTEM_INAPPROPRIATELY_REPAIRED;
    }

    public function getForClass7Advisory(): int
    {
        return self::ANTI_LOCK_BRAKING_SYSTEM_INAPPROPRIATELY_MODIFIED;
    }

    public function getForClass7Prs(): int
    {
        return self::TYRE_HAS_PLY_OR_CORDS_EXPOSED;
    }
}
