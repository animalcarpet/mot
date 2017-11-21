<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA;

class GroupAEuReasonForRejection implements GroupAEuReasonForRejectionInterface
{
    const NAIL_IN_TYRE = 10001;
    const BEHAT_TEST = 90000;

    public function getForClass1(): int
    {
        return self::BEHAT_TEST;
    }

    public function getForClass1Dangerous(): int
    {

    }

    public function getForClass1Advisory(): int
    {
        return self::NAIL_IN_TYRE;
    }

    public function getForClass1Prs(): int
    {
        return self::BEHAT_TEST;
    }

    public function getForClass2(): int
    {
        return self::BEHAT_TEST;
    }

    public function getForClass2Dangerous(): int
    {

    }

    public function getForClass2Advisory(): int
    {
        return self::NAIL_IN_TYRE;
    }

    public function getForClass2Prs(): int
    {
        return self::BEHAT_TEST;
    }
}
