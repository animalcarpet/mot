<?php

namespace NotificationApiTest\Service;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use NotificationApi\Service\PositionRemovalNotificationService;

/**
 * Class PositionRemovalNotificationServiceTest.
 *
 * Unit testing PositionRemovalNotificationService
 */
class PositionRemovalNotificationServiceTest extends AbstractServiceTestCase
{
    // Scenario 1:
    public function testScenarioOneDvsaOrgSiteRemovingFromSite()
    {
        // When: User who is DVSA/Org/Site removing role from site
        $service = new PositionRemovalNotificationService($this->dummyRoles());

        $siteRemoval = $service->getSiteRoleRemovalContactText(1);

        // Then: Notification message should display to contact VTS
        $this->assertEquals(PositionRemovalNotificationService::VTS, $siteRemoval);
    }

    // Scenario 2:
    public function testScenarioTwoDvsaOrgSiteRemovingFromOrganisation()
    {
        // When: User who is DVSA/Org/Site removing role from organisation
        $service = new PositionRemovalNotificationService($this->dummyRoles());

        $orgRemoval = $service->getOrganisationRoleRemovalContactText(9);

        // Then: Notification message should display to contact Organisation
        $this->assertEquals(PositionRemovalNotificationService::ORGANISATION, $orgRemoval);
    }

    // Scenario 3:
    public function testScenarioThreeDvsaOrgRemovingFromOrganisation()
    {
        $roles = $this->dummyRoles();
        $roles['sites'] = [];

        // When: User who is DVSA/Org removing role from organisation
        $service = new PositionRemovalNotificationService($roles);

        $orgRemoval = $service->getOrganisationRoleRemovalContactText(9);

        // Then: Notification message should display to contact Organisation
        $this->assertEquals(PositionRemovalNotificationService::ORGANISATION, $orgRemoval);
    }

    // Scenario 4:
    public function testScenarioFourOrgRemovingFromSite()
    {
        $roles = $this->dummyRoles();
        $roles['system'] = [];
        $roles['sites'] = [1 => ['roles' => []]];

        // When: User who is Org removing role from site
        $service = new PositionRemovalNotificationService($roles);

        $siteRemoval = $service->getSiteRoleRemovalContactText(1);

        // Then: Notification message should display to contact Organisation
        $this->assertEquals(PositionRemovalNotificationService::ORGANISATION, $siteRemoval);
    }

    // Scenario 5:
    public function testScenarioFiveOrgRemovingFromSite()
    {
        $roles = $this->dummyRoles();
        $roles['sites'] = [];
        $roles['system'] = [];

        // When: User who is Org removing role from organisation
        $service = new PositionRemovalNotificationService($roles);

        $orgRemoval = $service->getOrganisationRoleRemovalContactText(10);

        // Then: Notification message should display to contact Organisation
        $this->assertEquals(PositionRemovalNotificationService::ORGANISATION, $orgRemoval);
    }

    // Scenario 6:
    public function testScenarioSixSiteRemovingFromSite()
    {
        $roles = $this->dummyRoles();
        $roles['organisations'] = [];
        $roles['system'] = [];

        // When: User who is Site removing role from site
        $service = new PositionRemovalNotificationService($roles);

        $siteRemoval = $service->getSiteRoleRemovalContactText(1);

        // Then: Notification message should display to contact Site
        $this->assertEquals(PositionRemovalNotificationService::VTS, $siteRemoval);
    }

    // Scenario 7:
    public function testScenarioSevenDvsaRemovingFromOrganisation()
    {
        $roles = $this->dummyRoles();
        $roles['organisations'] = [];
        $roles['sites'] = [];

        // When: User who is DVSA removing role from organisation
        $service = new PositionRemovalNotificationService($roles);

        $siteRemoval = $service->getOrganisationRoleRemovalContactText('test2');

        // Then: Notification message should display to contact DVSA local office
        $this->assertEquals(PositionRemovalNotificationService::DVSA_OFFICE, $siteRemoval);
    }

    // Scenario 8:
    public function testScenarioEightDvsaRemovingFromSite()
    {
        $roles = $this->dummyRoles();
        $roles['organisations'] = [];
        $roles['sites'] = [];

        // When: User who is DVSA removing role from organisation
        $service = new PositionRemovalNotificationService($roles);

        $siteRemoval = $service->getSiteRoleRemovalContactText('test2');

        // Then: Notification message should display to contact DVSA local office
        $this->assertEquals(PositionRemovalNotificationService::DVSA_OFFICE, $siteRemoval);
    }

    // mock array of roles
    private function dummyRoles()
    {
        $roles = [
            'normal' => [
                'roles' => [
                    'DVSA-AREA-OFFICE-1',
                    'TESTER',
                    'SITE-ADMIN',
                    'SITE-MANAGER',
                ],
            ],
            'organisations' => [
                '9' => [
                    'roles' => [
                        'AUTHORISED-EXAMINER-DELEGATE',
                        'AUTHORISED-EXAMINER-DESIGNATED-MANAGER',
                    ],
                ],
                '10' => [
                    'roles' => [
                        'AUTHORISED-EXAMINER-DESIGNATED-MANAGER',
                    ],
                ],
                '12' => [
                    'roles' => [
                        'AUTHORISED-EXAMINER-DESIGNATED-MANAGER',
                    ],
                ],
            ],
            'sites' => [
                1 => [
                    'roles' => [
                        'TESTER',
                        'SITE-MANAGER',
                    ],
                ],
            ],
            'siteOrganisationMap' => [
                1 => [
                    9,
                ],
            ],
        ];

        return $roles;
    }
}
