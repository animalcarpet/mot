<?php
namespace DvsaCommon\Messages;

use DvsaCommon\Enum\MotTestStatusName;

// Used both in api and frontend
/**
 * Maps test status to error message.
 */
class InvalidTestStatus
{
    const ERROR_MESSAGE_TEST_ABORTED_BY_VE = "This test has been aborted by DVSA and cannot be continued";
    const ERROR_MESSAGE_TEST_COMPLETE = "This test is completed and cannot be changed";

    public static function getMessage($status)
    {
        if ($status === MotTestStatusName::ABORTED_VE) {
            return self::ERROR_MESSAGE_TEST_ABORTED_BY_VE;
        } elseif ($status !== MotTestStatusName::ACTIVE) {
            return self::ERROR_MESSAGE_TEST_COMPLETE;
        } else {
            return '';
        }
    }
}
