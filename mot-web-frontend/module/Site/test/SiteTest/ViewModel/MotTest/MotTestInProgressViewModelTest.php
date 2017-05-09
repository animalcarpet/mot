<?php

namespace SiteTest\ViewModel\MotTest;

use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;
use Site\ViewModel\MotTest\MotTestInProgressViewModel;

class MotTestInProgressViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function testGetVrmOrItsAbsentReason_vrmPresent()
    {
        $reg = 'REG';
        $dto = (new MotTestInProgressDto())->setVehicleRegisteredNumber($reg);
        $this->assertEquals($reg, (new MotTestInProgressViewModel($dto))->getVrmOrItsAbsentReason());
    }

    public function testGetVrmOrItsAbsentReason_reasonPresent()
    {
        $reason = 'REASON';
        $dto = (new MotTestInProgressDto())->setEmptyVrmReasonName($reason);
        $this->assertEquals($reason, (new MotTestInProgressViewModel($dto))->getVrmOrItsAbsentReason());
    }
}
