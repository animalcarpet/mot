<?php

namespace UserAdminTest\Presenter;

use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use DvsaCommon\Validator\EmailAddressValidator;
use UserAdmin\Presenter\UserProfilePresenter;

/**
 * Unit tests for UserProfilePresenter.
 */
class UserProfilePresenterTest extends \PHPUnit_Framework_TestCase
{
    public function testDisplayInformation()
    {
        $presenter = UserProfilePresenterBuilder::of($this)
            ->withTradeRoles()->noDvsaRoles()->isDvsaUser()->setPersonId(1)->build();

        $this->assertEquals(1, $presenter->getPersonId());
        $this->assertEquals('Username', $presenter->displayUserName());
        $this->assertEquals('Mrs Harriet Jones', $presenter->displayTitleAndFullName());
        $this->assertEquals('29 May 1992', $presenter->displayDateOfBirth());
        $this->assertEquals('Harriet Jones', $presenter->displayFullName());
        $this->assertEquals('drivingLicenceNumber', $presenter->displayDrivingLicenceNumber());
        $this->assertEquals(
            'Address line 1, Address line 2, Address line 3, Address line 4, Bristol, Postcode',
            $presenter->displayAddressLine()
        );
        $this->assertEquals('Address line 1', $presenter->displayAddressLine1());
        $this->assertEquals('Address line 2', $presenter->displayAddressLine2());
        $this->assertEquals('Address line 3', $presenter->displayAddressLine3());
        $this->assertEquals('Address line 4', $presenter->displayAddressLine4());
        $this->assertEquals('Postcode', $presenter->displayPostcode());
        $this->assertEquals('Bristol', $presenter->displayTown());
        $this->assertEquals(
            'Address line 1, Address line 2, Address line 3, Address line 4, Bristol, Postcode',
            $presenter->displayFullAddress()
        );
        $this->assertEquals('userprofilepresentertest@'.EmailAddressValidator::TEST_DOMAIN, $presenter->displayEmail());
        $this->assertEquals('+768-45-4433630', $presenter->displayTelephone());

        $this->assertEquals('/event/list/person/1', $presenter->displayEventsHistoryLink());

        $roles = [
            0 => [
                'roles' => ['Authorised Examiner Designated Manager'],
                'route' => AuthorisedExaminerUrlBuilderWeb::of(0),
            ],
            1 => [
                'roles' => ['Tester'],
                'route' => VehicleTestingStationUrlBuilderWeb::byId(0),
            ],
        ];
        $this->assertEquals($roles, $presenter->getSiteAndOrganisationRoles());
        $this->assertEquals(UserProfilePresenter::DVSA_PROFILE_TEMPLATE, $presenter->getTemplate());
    }

    public function testGetTemplateWhenDefaultSettingsShouldReturnUnrestrictedProfile()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->build();

        $this->assertEquals(UserProfilePresenter::UNRESTRICTED_PROFILE_TEMPLATE, $presenter->getTemplate());
    }

    public function testGetTemplateWhenIsDvsaUserShouldReturnUnrestrictedDvsaProfile()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->isDvsaUser()->build();

        $this->assertEquals(UserProfilePresenter::DVSA_PROFILE_TEMPLATE, $presenter->getTemplate());
    }

    public function testCanDisplayDvsaRoleSectionWhenNoRolesShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->noTradeRoles()->noDvsaRoles()
            ->userHasPermissionToReadDvsaRoles()->build();

        $this->assertTrue($presenter->canDisplayDvsaRoleSection());
    }

    public function testCanDisplayDvsaRoleSectionWhenTradeRolesAssignedShouldReturnFalse()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->withTradeRoles()->noDvsaRoles()
            ->userHasPermissionToReadDvsaRoles()->build();

        $this->assertFalse($presenter->canDisplayDvsaRoleSection());
    }

    public function testCanDisplayDvsaRoleSectionWhenDvsaRolesAssignedShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->noTradeRoles()->withDvsaRoles()
            ->userHasPermissionToReadDvsaRoles()->build();

        $this->assertTrue($presenter->canDisplayDvsaRoleSection());
    }

    public function testCanDisplayDvsaRoleSectionWhenTradeRolesAndDvsaRolesAssignedShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->withTradeRoles()->withDvsaRoles()
            ->userHasPermissionToReadDvsaRoles()->build();

        $this->assertTrue($presenter->canDisplayDvsaRoleSection());
    }

    public function testIsManagingOwnRolesShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->userIsViewingHisOwnProfilePage(false)->build();

        $this->assertTrue($presenter->isManagingOwnRoles());
    }

    public function testIsManagingOwnRolesWhenUserSeesHisOwnProfileShouldReturnFalse()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->userIsViewingHisOwnProfilePage(true)->build();

        $this->assertFalse($presenter->isManagingOwnRoles());
    }

    public function testCanManageDvsaRolesWhenUserHasPermissionToManageDvsaRolesShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->userHasManageDvsaRolesPermission()->build();

        $this->assertTrue($presenter->canManageDvsaRoles());
    }

    public function testCanManageDvsaRolesWhenUserHasNotPermissionToManageDvsaRolesShouldReturnFalse()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->userHasManageDvsaRolesPermission(false)->build();

        $this->assertFalse($presenter->canManageDvsaRoles());
    }

    public function testHasDvsaRolesWhenUserHasDvsaRolesShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->withDvsaRoles()->build();

        $this->assertTrue($presenter->hasDvsaRoles());
    }

    public function testCanReadDvsaRolesWhenUserHasPermissionShouldReturnTrue()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->userHasPermissionToReadDvsaRoles()->build();

        $this->assertTrue($presenter->canReadDvsaRoles());
    }

    public function testCanReadDvsaRolesWhenUserHasNotPermissionShouldReturnFalse()
    {
        $presenter = UserProfilePresenterBuilder::of($this)->userHasPermissionToReadDvsaRoles(false)->build();

        $this->assertFalse($presenter->canReadDvsaRoles());
    }
}
