<?php

namespace OrganisationTest\ViewModel\View\Index;

use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use Organisation\ViewModel\View\Index\EmployeeViewModel;

/**
 * Class EmployeeViewModelTest.
 */
class EmployeeViewModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var $model EmployeeViewModel */
    private $model;

    public function setUp()
    {
        $this->model = new EmployeeViewModel(new PersonDto());
    }

    public function testGetterSetterShouldBeOk()
    {
        $this->model->setPositionId(1);
        $this->assertEquals(1, $this->model->getPositionId());
    }

    public function testGetDisplayRolesNoRolesShouldBeOk()
    {
        $this->assertEmpty($this->model->getDisplayRoles());
    }

    public function testGetDisplayRolesOneRoleShouldBeOk()
    {
        $role = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE;

        $this->model->addPosition((new OrganisationPositionDto())->setRole($role));

        $this->assertEquals($role, $this->model->getDisplayRoles());
    }

    public function testGetDisplayRolesManyRolesShouldBeOk()
    {
        $role = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE;

        $this->model->addPosition((new OrganisationPositionDto())->setRole($role));
        $this->model->addPosition((new OrganisationPositionDto())->setRole($role));

        $this->assertEquals($role.', '.$role, $this->model->getDisplayRoles());
    }
}
