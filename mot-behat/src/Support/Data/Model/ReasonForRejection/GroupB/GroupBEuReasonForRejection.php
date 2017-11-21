<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB;

class GroupBEuReasonForRejection implements GroupBEUReasonForRejectionInterface
{
    const FRACTURED = 20512;
    const DEFORMED = 20513;
    const MODIFICATION_UNSAFE = 20553;
    const FRACTURED_TO_THE_EXTENT_THAT_STEERING_IS_AFFECTED = 20526;
    const POWER_STEERING_PUMP_INSECURE_AND_STEERING_ADVERSELY_AFFECTED = 20608;
    const POWER_STEERING_PUMP_FRACTURED = 20129;
    const POWER_STEERING_PUMP_LEAKING = 20595;
    const POWER_STEERING_PUMP_INSECURE = 20602;
    const DRIVERS_DOOR_LIKELY_TO_OPEN_INADVERTENTLY = 21717;
    const DRIVERS_DOOR_HINGE_MISSING = 21729;
    const DRIVERS_DOOR_CATCH_MISSING = 21730;
    const DRIVERS_DOOR_PILLAR_DETERIORATED = 21722;
    const AXLE_FRACTURED = 21038;
    const AXLE_INSECURE = 21039;
    const AXLE_WITH_LOOSE_FIXING_BOLTS = 21040;
    const AXLE_HAS_EXCESSIVE_VERTICAL_MOVEMENT= 21052;

    public function getForClass3(): int
    {
        return self::FRACTURED;
    }

    public function getForClass3Dangerous(): int
    {
        return self::FRACTURED_TO_THE_EXTENT_THAT_STEERING_IS_AFFECTED;
    }

    public function getForClass3Advisory(): int
    {
        return self::DEFORMED;
    }

    public function getForClass3Prs(): int
    {
        return self::MODIFICATION_UNSAFE;
    }

    public function getForClass4(): int
    {
        return self::POWER_STEERING_PUMP_FRACTURED;
    }

    public function getForClass4Dangerous(): int
    {
        return self::POWER_STEERING_PUMP_INSECURE_AND_STEERING_ADVERSELY_AFFECTED;
    }

    public function getForClass4Advisory(): int
    {
        return self::POWER_STEERING_PUMP_LEAKING;
    }

    public function getForClass4Prs(): int
    {
        return self::POWER_STEERING_PUMP_INSECURE;
    }

    public function getForClass5(): int
    {
        return self::DRIVERS_DOOR_HINGE_MISSING;
    }
    public function getForClass5Dangerous(): int
    {
        return self::DRIVERS_DOOR_LIKELY_TO_OPEN_INADVERTENTLY;
    }

    public function getForClass5Advisory(): int
    {
        return self::DRIVERS_DOOR_PILLAR_DETERIORATED;
    }

    public function getForClass5Prs(): int
    {
        return self::DRIVERS_DOOR_CATCH_MISSING;
    }

    public function getForClass7(): int
    {
        return self::AXLE_INSECURE;
    }
    public function getForClass7Dangerous(): int
    {
        return self::AXLE_FRACTURED;
    }

    public function getForClass7Advisory(): int
    {
        return self::AXLE_HAS_EXCESSIVE_VERTICAL_MOVEMENT;
    }

    public function getForClass7Prs(): int
    {
        return self::AXLE_WITH_LOOSE_FIXING_BOLTS;
    }
}
