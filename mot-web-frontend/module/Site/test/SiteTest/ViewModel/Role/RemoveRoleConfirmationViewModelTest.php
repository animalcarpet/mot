<?php

namespace SiteTest\ViewModel;

use Site\ViewModel\Role\RemoveRoleConfirmationViewModel;

/**
 * Class RemoveRoleConfirmationViewModelTest.
 */
class RemoveRoleConfirmationViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function test_getterSetter_shouldBeOk()
    {
        $model = (new RemoveRoleConfirmationViewModel())
            ->setEmployeeId(1)
            ->setSiteName(2)
            ->setSiteId(3)
            ->setRoleName(4)
            ->setEmployeeName(5)
            ->setPositionId(6)
            ->setActiveMotTestNumber(123);
        $this->assertEquals(1, $model->getEmployeeId());
        $this->assertEquals(2, $model->getSiteName());
        $this->assertEquals(3, $model->getSiteId());
        $this->assertEquals(4, $model->getRoleName());
        $this->assertEquals(5, $model->getEmployeeName());
        $this->assertEquals(6, $model->getPositionId());
        $this->assertEquals(true, $model->hasActiveMotTest());
    }
}
