<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA;

class GroupAPreEuDirectiveReasonForRejection implements GroupAReasonForRejectionInterface
{
    const FAIRINGS_SO_LOCATED_THAT_IT_IS_LIKELY_TO_IMPEDE_THE_STEERING = 236;
    const FAIRINGS_INSECURE_AND_LIKELY_TO_IMPEDE_THE_STEERING = 235;
    const FOOTREST_MISSING = 659;
    const BRAKE_LEVER_INSECURE = 356;
    const BRAKE_LEVER_PIVOTS_EXCESSIVELY_WORN = 373;
    const BRAKE_LEVER_CRACKED = 360;

    public function getForClass1(): int
    {
        return self::FAIRINGS_SO_LOCATED_THAT_IT_IS_LIKELY_TO_IMPEDE_THE_STEERING;
    }

    public function getForClass1Advisory(): int
    {
        return self::FAIRINGS_INSECURE_AND_LIKELY_TO_IMPEDE_THE_STEERING;
    }

    public function getForClass1Prs(): int
    {
        return self::FOOTREST_MISSING;
    }

    public function getForClass2(): int
    {
        return self::BRAKE_LEVER_INSECURE;
    }

    public function getForClass2Advisory(): int
    {
        return self::BRAKE_LEVER_PIVOTS_EXCESSIVELY_WORN;
    }

    public function getForClass2Prs(): int
    {
        return self::BRAKE_LEVER_CRACKED;
    }
}
