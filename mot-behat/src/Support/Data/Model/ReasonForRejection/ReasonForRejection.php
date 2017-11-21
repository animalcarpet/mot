<?php

namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection;

use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA\GroupAEuReasonForRejection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA\GroupAPreEuDirectiveReasonForRejection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA\GroupAReasonForRejectionInterface;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB\GroupBEuReasonForRejection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB\GroupBPreEuDirectiveReasonForRejection;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupB\GroupBReasonForRejectionInterface;

class ReasonForRejection
{
    private static $groupA;
    private static $groupB;

    public static function getGroupA(): GroupAReasonForRejectionInterface
    {
        if (static::$groupA === null) {
            static::createGroupA();
        }

        return static::$groupA;
    }

    private static function createGroupA()
    {
        if (EuReasonForRejectionToggle::isEnabled()) {
            static::$groupA = new GroupAEuReasonForRejection();
        } else {
            static::$groupA = new GroupAPreEuDirectiveReasonForRejection();
        }
    }

    public static function getGroupB(): GroupBReasonForRejectionInterface
    {
        if (static::$groupB === null) {
            static::createGroupB();
        }

        return static::$groupB;
    }

    private static function createGroupB()
    {
        if (EuReasonForRejectionToggle::isEnabled()) {
            static::$groupB = new GroupBEuReasonForRejection();
        } else {
            static::$groupB = new GroupBPreEuDirectiveReasonForRejection();
        }
    }
}
