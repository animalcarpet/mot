<?php

namespace OrganisationTest\ViewModel;

use Organisation\ViewModel\View\Role\RemoveRoleConfirmationViewModel;

/**
 * Class RemoveRoleConfirmationViewModelTest.
 */
class RemoveRoleConfirmationViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function test_getterSetter_shouldBeOk()
    {
        $model = (new RemoveRoleConfirmationViewModel())
            ->setEmployeeId(1)
            ->setOrganisationName(2)
            ->setOrganisationId(3)
            ->setRoleName(4)
            ->setEmployeeName(5)
            ->setRoleId(6);
        $this->assertEquals(1, $model->getEmployeeId());
        $this->assertEquals(2, $model->getOrganisationName());
        $this->assertEquals(3, $model->getOrganisationId());
        $this->assertEquals(4, $model->getRoleName());
        $this->assertEquals(5, $model->getEmployeeName());
        $this->assertEquals(6, $model->getRoleId());
    }
}
