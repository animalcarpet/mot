<?php

namespace DvsaCommonTest\Model;

use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Model\TwoFaTesterApplicantRole;

class TwoFaTesterApplicantRoleTest extends \PHPUnit_Framework_TestCase
{
    public function testTesterActiveRoleIsANewTesterRole()
    {
        $this->assertTrue(TwoFaTesterApplicantRole::isTwoFaTesterApplicantRole(RoleCode::TESTER_ACTIVE));
    }

    public function testTesterApplicantDemoTestRequiredRoleIsANewTesterRole()
    {
        $this->assertTrue(TwoFaTesterApplicantRole::isTwoFaTesterApplicantRole(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED));
    }

    public function testAnotherRoleIsNotANewTesterRole()
    {
        $this->assertFalse(TwoFaTesterApplicantRole::isTwoFaTesterApplicantRole(RoleCode::TESTER_INACTIVE));
    }

    public function testNewTesterRolesContainsANewTesterRoleWithCorrectRole()
    {
        $roles = [
            RoleCode::AREA_OFFICE_2,
            RoleCode::TESTER_ACTIVE,
        ];

       $this->assertTrue(TwoFaTesterApplicantRole::containsTwoFaTesterApplicantRole($roles));
    }

    public function testNewTesterRolesContainsANewTesterRoleWithIncorrectRole()
    {
        $roles = [
            RoleCode::ASSESSMENT,
            RoleCode::AREA_OFFICE_1,
        ];

        $this->assertFalse(TwoFaTesterApplicantRole::containsTwoFaTesterApplicantRole($roles));
    }
}