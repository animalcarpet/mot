<?php

namespace UserApiTest\Dashboard\Dto;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use UserApi\Dashboard\Dto\DashboardData;

/**
 * Unit tests for Special notice dto
 */
class DashboardDataTest extends AbstractServiceTestCase
{
    public function test_toArray_basicData_shouldBeOk()
    {
        $specialNotice = SpecialNoticeTest::getInputUnreadOverdueDeadline();
        $overdueSpecialNotices = array_combine(VehicleClassCode::getAll(), array_fill(0,count(VehicleClassCode::getAll()),0));

        $authorisationMock = new GrantAllAuthorisationServiceStub();
        $motTestType = MotTestTypeCode::NORMAL_TEST;

        $dashboard = new DashboardData([], $specialNotice, $overdueSpecialNotices, [], 0, 3, 4, 5, true, true, $motTestType, $authorisationMock, 1, true);
        $this->assertWellFormedData($dashboard->toArray());
    }

    private function assertWellFormedData($data)
    {
        $this->assertTrue(
            is_array($data)
            && isset($data['hero'])
            && isset($data['authorisedExaminers'])
            && is_array($data['authorisedExaminers'])
            && isset($data['specialNotice'])
            && isset($data['overdueSpecialNotices'])
            && is_array($data['specialNotice'])
            && isset($data['notifications'])
            && is_array($data['notifications'])
            && isset($data['inProgressTestNumber'])
            && isset($data['inProgressDemoTestNumber'])
            && isset($data['inProgressNonMotTestNumber'])
        );
    }
}
