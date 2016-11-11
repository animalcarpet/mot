<?php

namespace DashboardTest\Model;

use Dashboard\Model\Dashboard;
use DashboardTest\Data\ApiDashboardResourceTest;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;

/**
 * Unit test for Dashboard\Model\Dashboard
 */
class DashboardTest extends \PHPUnit_Framework_TestCase
{
    public function test_getOverallSlotCount_forOneAe_shouldReturn100()
    {
        $this->runTest_getOverallSlotCount_forVarNoOfAesAndSlots_shouldReturnExcepted(1, 100, 100);
    }

    public function test_getOverallSlotCount_forManyAes_shouldReturnExcepted()
    {
        $this->runTest_getOverallSlotCount_forVarNoOfAesAndSlots_shouldReturnExcepted(4, 100, 400);
    }

    private function runTest_getOverallSlotCount_forVarNoOfAesAndSlots_shouldReturnExcepted($aeCount, $slots, $expected)
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForAedm($aeCount, ['slots' => $slots]));

        $this->assertEquals($expected, $dashboard->getOverallSlotCount());
    }

    public function test_getOverallSiteCount_forOneAe_shouldReturnExpected()
    {
        $this->runTest_getOverallSiteCount_forVariousNumberOfAesAndVts_shouldReturnExpected(1, 4, 4);
    }

    public function test_getOverallSiteCount_forManyAe_shouldReturnExpected()
    {
        $this->runTest_getOverallSiteCount_forVariousNumberOfAesAndVts_shouldReturnExpected(4, 8, 32);
    }

    public function test_getters()
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForAedm(1, ['vtsCount' => 1]));
        $this->assertCount(0, $dashboard->getNotifications());
        $this->assertEquals('aedm', $dashboard->getHero());
    }

    private function runTest_getOverallSiteCount_forVariousNumberOfAesAndVts_shouldReturnExpected(
        $aeCount,
        $vtsCount,
        $expected
    )
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForAedm($aeCount, ['vtsCount' => $vtsCount]));

        $this->assertEquals($expected, $dashboard->getOverallSiteCount());
    }

    public function test_getOverallAuthoriseExaminerCount_noAe_shouldReturn0()
    {
        $this->runTest_getOverallAuthoriseExaminerCount_shouldReturnExpected(0);
    }

    public function test_getOverallAuthoriseExaminerCount_oneAe_shouldReturn1()
    {
        $this->runTest_getOverallAuthoriseExaminerCount_shouldReturnExpected(1);
    }

    public function test_getOverallAuthoriseExaminerCount_manyAe_shouldReturn1()
    {
        $this->runTest_getOverallAuthoriseExaminerCount_shouldReturnExpected(rand(10, 1000));
    }

    private function runTest_getOverallAuthoriseExaminerCount_shouldReturnExpected($expected)
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForAedm($expected));
        $this->assertEquals($expected, $dashboard->getOverallAuthoriseExaminerCount());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Display permission this-does-not-exist does not exist
     */
    public function test_canDisplay_nonExistingPermission_shouldThrowLogicException()
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForUser());
        $dashboard->canDisplay('this-does-not-exist');
    }

    public function test_specialNotice_toArray_shouldBeOk()
    {
        $input_expected = $this->getSpecialNoticeInput();

        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForUser($input_expected));

        $this->assertSame($input_expected, $dashboard->getSpecialNotice()->toArray());
    }

    public function test_specialNotice_toArray_shouldNotBeOk()
    {
        $input_expected = $this->getSpecialNoticeInput();

        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForUser($input_expected));

        $this->assertNotSame([], $dashboard->getSpecialNotice()->toArray());
    }

    public function test_inProgressTestId_isNumericIfNotNull()
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForUser());
        $this->assertTrue($dashboard->getInProgressTestNumber() == null || is_numeric($dashboard->getInProgressTestNumber()));
    }

    public function test_isAedm_returnsTrue()
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForAedm(2));
        $this->assertTrue($dashboard->isAedm());
    }

    public function test_isAedm_returnsFalse()
    {
        $dashboardData = ApiDashboardResourceTest::getTestDataForAedm(1);
        $dashboardData['authorisedExaminers'][0]['position'] =
            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE;
        $dashboard = new Dashboard($dashboardData);
        $this->assertFalse($dashboard->isAedm());
    }

    public function test_isTesterAtAnySite_returnsTrue()
    {
        $dashboardData = ApiDashboardResourceTest::getTestDataForAedm(2);
        $dashboardData['authorisedExaminers'][0]['sites'][0]['positions'] = [SiteBusinessRoleCode::TESTER];
        $dashboard = new Dashboard($dashboardData);
        $this->assertTrue($dashboard->isTesterAtAnySite());
    }

    public function test_isTesterAtAnySite_returnsFalse()
    {
        $dashboardData = ApiDashboardResourceTest::getTestDataForAedm(2);
        $dashboard = new Dashboard($dashboardData);
        $this->assertFalse($dashboard->isTesterAtAnySite());
    }

    public function test_hasInProgressTest_trueIfInProgressTestIdSet()
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForUser());
        $this->assertTrue($dashboard->hasTestInProgress());
    }

    public function test_hasInProgressTest_falseIfInProgressTestIdNotSet()
    {
        $testData = ApiDashboardResourceTest::getTestDataForAedm();
        $testData['inProgressTestNumber'] = null;
        $dashboard = new Dashboard($testData);

        $this->assertFalse($dashboard->hasTestInProgress());
    }

    public function test_hasInProgressDemoTest_trueIfInProgressTestIdSet()
    {
        $dashboard = new Dashboard(ApiDashboardResourceTest::getTestDataForUser());
        $this->assertTrue($dashboard->hasDemoTestInProgress());
    }

    public function test_hasInProgressDemoTest_falseIfInProgressTestIdNotSet()
    {
        $testData = ApiDashboardResourceTest::getTestDataForAedm();
        $testData['inProgressDemoTestNumber'] = null;
        $dashboard = new Dashboard($testData);

        $this->assertFalse($dashboard->hasDemoTestInProgress());
    }

    private function getSpecialNoticeInput()
    {
        return [
            'unreadCount' => 1,
            'daysLeftToView' => 3,
            'overdueCount' => 0,
        ];
    }

    public function test_isInProgressTestARetest_trueIfTypeIsRetest()
    {
        $testData = ApiDashboardResourceTest::getTestDataForAedm();
        $testData['inProgressTestTypeCode'] = 'RT';
        $dashboard = new Dashboard($testData);
        $this->assertTrue($dashboard->isInProgressTestARetest());
    }

    public function test_isInProgressTestARetest_falseIfTypeIsNotRetest()
    {
        $testData = ApiDashboardResourceTest::getTestDataForAedm();
        $dashboard = new Dashboard($testData);
        $this->assertFalse($dashboard->isInProgressTestARetest());
    }

    public function test_getEnterTestResultsLabel_shouldReturnExpectedIfTypeIsRetest()
    {
        $testData = ApiDashboardResourceTest::getTestDataForAedm();
        $testData['inProgressTestTypeCode'] = 'RT';
        $dashboard = new Dashboard($testData);
        $this->assertSame("Enter retest results", $dashboard->getEnterTestResultsLabel());
    }

    public function test_getEnterTestResultsLabel_shouldReturnExpectedIfTypeIsNotRetest()
    {
        $testData = ApiDashboardResourceTest::getTestDataForAedm();
        $dashboard = new Dashboard($testData);
        $this->assertSame("Enter test results", $dashboard->getEnterTestResultsLabel());
    }

    public function test_InProgressNonMotTest_shouldBeAvailableIfValueIsSet()
    {
        $testData = ApiDashboardResourceTest::getTestDataForVehicleExaminer();
        $dashboard = new Dashboard($testData);
        $this->assertTrue($dashboard->hasNonMotTestInProgress());
        $this->assertEquals($testData['inProgressNonMotTestNumber'], $dashboard->getInProgressNonMotTestNumber());
    }

    public function test_InProgressNonMotTest_shouldNotBeAvailableIfValueIsNotSet()
    {
        $testData = ApiDashboardResourceTest::getTestDataForUser();
        $dashboard = new Dashboard($testData);
        $this->assertFalse($dashboard->hasNonMotTestInProgress());
        $this->assertNull($dashboard->getInProgressNonMotTestNumber());
    }
}
