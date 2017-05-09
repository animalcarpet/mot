<?php

namespace UserApiTest\Dashboard\Dto;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use UserApi\Dashboard\Dto\SpecialNotice;

/**
 * Unit tests for Special notice dto.
 */
class SpecialNoticeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function test_gettersSetters_shouldBeOk()
    {
        $input = self::getInputUnreadOverdueDeadline();

        $specialNotice = new SpecialNotice($input);

        $this->assertEquals($input['overdueCount'], $specialNotice->getOverdueCount());
        $this->assertEquals($input['unreadCount'], $specialNotice->getUnreadCount());
    }

    public function test_getDaysLeftToView_unreadCount0acknowledgementDeadlineNull_shouldReturn0()
    {
        $this->runTest_getDaysLeftToView_expected_unread_deadline(0);
    }

    public function test_getDaysLeftToView_unreadCount0acknowledgementDeadlineNotNull_shouldReturn0()
    {
        $this->runTest_getDaysLeftToView_expected_unread_deadline(0, 0, $this->now());
    }

    public function test_getDaysLeftToView_unreadCount1acknowledgementDeadlineNow_shouldReturn0()
    {
        $this->runTest_getDaysLeftToView_expected_unread_deadline(0, 1, $this->now());
    }

    public function test_getDaysLeftToView_unreadCount1acknowledgementDeadlineInFuture_shouldReturn7()
    {
        $this->runTest_getDaysLeftToView_expected_unread_deadline(7, 1, $this->now('+7 day'));
    }

    public function test_getDaysLeftToView_unreadCount1acknowledgementDeadlineInPast_shouldReturnMinus3()
    {
        $this->runTest_getDaysLeftToView_expected_unread_deadline(-3, 1, $this->now('-3 day'));
    }

    private function runTest_getDaysLeftToView_expected_unread_deadline($expected, $unread = 0, $deadline = null)
    {
        $input = self::getInputUnreadOverdueDeadline($unread, 1, $deadline);

        $specialNotice = new SpecialNotice($input);

        $expectedOutput = $input;
        unset($expectedOutput['acknowledgementDeadline']);
        $expectedOutput['daysLeftToView'] = $this->getDaysDifference($deadline);

        $this->assertEquals($expected, $specialNotice->getDaysLeftToView());
        $this->assertEquals($expectedOutput, $specialNotice->toArray());
    }

    private function getDaysDifference($deadline)
    {
        $deadline = (null !== $deadline) ? $deadline : $this->now();

        return DateUtils::getDaysDifference($this->now(), $deadline);
    }

    private function now($modify = null)
    {
        $date = DateUtils::today();
        if (null !== $modify) {
            $date->modify($modify);
        }

        return $date;
    }

    public static function getInputUnreadOverdueDeadline($unread = 0, $overdue = 1, $deadline = null)
    {
        $date = (null !== $deadline) ? DateTimeApiFormat::date($deadline) : null;

        return [
            'unreadCount' => $unread,
            'overdueCount' => $overdue,
            'acknowledgementDeadline' => $date,
        ];
    }
}
