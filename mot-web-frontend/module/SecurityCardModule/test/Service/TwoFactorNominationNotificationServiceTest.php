<?php

namespace Dvsa\Mot\Frontend\SecurityCardTest\Service;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaClient\Mapper\OrganisationPositionMapper;
use DvsaClient\Mapper\SitePositionMapper;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class TwoFactorNominationNotificationServiceTest extends PHPUnit_Framework_TestCase
{
    const NOMINEE_ID = 999;

    /**
     * @var ApiPersonalDetails
     */
    private $personalDetailsRepository;

    /**
     * @var OrganisationPositionMapper
     */
    private $organisationPositionRepository;

    /**
     * @var SitePositionMapper
     */
    private $sitePositionRepository;

    /**
     * @var array
     */
    private $pendingOrganisationRoles;

    /**
     * @var array
     */
    private $pendingSiteRoles;

    /**
     * @var int
     */
    private $pendingOrganisationRoleIndex;

    /**
     * @var int
     */
    private $pendingSiteRoleIndex;

    /**
     * @var bool
     */
    private $useEmptyPendingRolesResponse;

    public function setUp()
    {
        $this->personalDetailsRepository = XMock::of(ApiPersonalDetails::class);
        $this->organisationPositionRepository = XMock::of(OrganisationPositionMapper::class);
        $this->sitePositionRepository = XMock::of(SitePositionMapper::class);

        $this->pendingOrganisationRoles = [];
        $this->pendingSiteRoles = [];
        $this->pendingOrganisationRoleIndex = 0;
        $this->pendingSiteRoleIndex = 0;

        $this->useEmptyPendingRolesResponse = false;
    }

    public function testWhenOnePendingSiteRole_oneSiteNominationIsUpdated()
    {
        $this
            ->expectNoOrganisationRoleNominationUpdates()
            ->withPendingSiteRole(101, 'SITE-ADMIN')
            ->expectSiteRoleNominationUpdate(101, 'SITE-ADMIN');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenTwoPendingSiteRole_twoSiteNominationsAreUpdated()
    {
        $this
            ->expectNoOrganisationRoleNominationUpdates()
            ->withPendingSiteRole(101, 'SITE-ADMIN')
            ->withPendingSiteRole(102, 'SITE-ADMIN')
            ->expectSiteRoleNominationUpdate(101, 'SITE-ADMIN')
            ->expectSiteRoleNominationUpdate(102, 'SITE-ADMIN');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenTwoPendingRolesForTheSameSite_twoSiteNominationsAreUpdated()
    {
        $this
            ->expectNoOrganisationRoleNominationUpdates()
            ->withPendingSiteRole(101, 'SITE-ADMIN')
            ->withPendingSiteRole(101, 'SITE-MANAGER')
            ->expectSiteRoleNominationUpdate(101, 'SITE-ADMIN')
            ->expectSiteRoleNominationUpdate(101, 'SITE-MANAGER');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenOnePendingOrgRole_oneOrgNominationIsUpdated()
    {
        $this
            ->expectNoSiteRoleNominationUpdates()
            ->withPendingOrganisationRole(101, 'AEDM')
            ->expectOrganisationRoleNominationUpdate(101, 'AEDM');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenTwoPendingOrgRoles_twoOrgNominationsAreUpdated()
    {
        $this
            ->expectNoSiteRoleNominationUpdates()
            ->withPendingOrganisationRole(101, 'AEDM')
            ->withPendingOrganisationRole(102, 'AEDM')
            ->expectOrganisationRoleNominationUpdate(101, 'AEDM')
            ->expectOrganisationRoleNominationUpdate(102, 'AEDM');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenTwoPendingRolesForTheSameOrg_twoOrgNominationsAreUpdated()
    {
        $this
            ->expectNoSiteRoleNominationUpdates()
            ->withPendingOrganisationRole(101, 'AEDM')
            ->withPendingOrganisationRole(101, 'AED')
            ->expectOrganisationRoleNominationUpdate(101, 'AEDM')
            ->expectOrganisationRoleNominationUpdate(101, 'AED');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWithACombinationOfOrgAndSiteRoles_theCombinedNumberOfNominationsAreUpdated()
    {
        $this
            ->withPendingOrganisationRole(101, 'AEDM')
            ->withPendingOrganisationRole(102, 'AEDM')
            ->withPendingSiteRole(101, 'SITE-ADMIN')
            ->withPendingSiteRole(102, 'SITE-ADMIN')
            ->expectOrganisationRoleNominationUpdate(101, 'AEDM')
            ->expectOrganisationRoleNominationUpdate(102, 'AEDM')
            ->expectSiteRoleNominationUpdate(101, 'SITE-ADMIN')
            ->expectSiteRoleNominationUpdate(102, 'SITE-ADMIN');

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenNoPendingRoles_noNominationsAreUpdated()
    {
        $this
            ->withNoPendingRoles()
            ->expectNoOrganisationRoleNominationUpdates()
            ->expectNoSiteRoleNominationUpdates();

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testWhenPendingRoleResponseCompletelyEmpty_noNominationsAreUpdated()
    {
        $this
            ->withEmptyPendingRolesResponse()
            ->expectNoOrganisationRoleNominationUpdates()
            ->expectNoSiteRoleNominationUpdates();

        $this->buildService()->sendNotificationsForPendingNominations(self::NOMINEE_ID);
    }

    public function testHasPendingNominations_NoPendingRoles_returnsFalse()
    {
        $this->withNoPendingRoles();

        $actual = $this->buildService()->hasPendingNominations(self::NOMINEE_ID);

        $this->assertFalse($actual);
    }

    public function testHasPendingNominations_NoWithPendingSiteRole_ReturnsTrue()
    {
        $this->withPendingSiteRole(self::NOMINEE_ID, 'TESTER');

        $actual = $this->buildService()->hasPendingNominations(self::NOMINEE_ID);

        $this->assertTrue($actual);
    }

    public function testHasPendingNominations_NoWithPendingOrganisationRole_ReturnsTrue()
    {
        $this->withPendingOrganisationRole(self::NOMINEE_ID, 'AED');

        $actual = $this->buildService()->hasPendingNominations(self::NOMINEE_ID);

        $this->assertTrue($actual);
    }

    private function expectOrganisationRoleNominationUpdate($orgId, $roleCode)
    {
        $this->organisationPositionRepository
            ->expects($this->at($this->pendingOrganisationRoleIndex))
            ->method('updatePosition')
            ->with($orgId, self::NOMINEE_ID, $roleCode);

        ++$this->pendingOrganisationRoleIndex;

        return $this;
    }

    private function expectSiteRoleNominationUpdate($siteId, $roleCode)
    {
        $this->sitePositionRepository
            ->expects($this->at($this->pendingSiteRoleIndex))
            ->method('update')
            ->with($siteId, self::NOMINEE_ID, $roleCode);

        ++$this->pendingSiteRoleIndex;

        return $this;
    }

    private function expectNoOrganisationRoleNominationUpdates()
    {
        $this->organisationPositionRepository
            ->expects($this->never())
            ->method('updatePosition');

        return $this;
    }

    private function expectNoSiteRoleNominationUpdates()
    {
        $this->sitePositionRepository
            ->expects($this->never())
            ->method('update');

        return $this;
    }

    private function withPendingOrganisationRole($organisationId, $roleCode)
    {
        $this->pendingOrganisationRoles[] = [$organisationId, $roleCode];

        return $this;
    }

    private function withPendingSiteRole($siteId, $roleCode)
    {
        $this->pendingSiteRoles[] = [$siteId, $roleCode];

        return $this;
    }

    private function withNoPendingRoles()
    {
        $this->pendingOrganisationRoles = [];
        $this->pendingSiteRoles = [];

        return $this;
    }

    private function withEmptyPendingRolesResponse()
    {
        $this->useEmptyPendingRolesResponse = true;

        return $this;
    }

    private function buildService()
    {
        $this->personalDetailsRepository
            ->expects($this->any())
            ->method('getPendingRolesForPerson')
            ->willReturn($this->buildPendingRolesResponse());

        return new TwoFactorNominationNotificationService(
            $this->personalDetailsRepository,
            $this->organisationPositionRepository,
            $this->sitePositionRepository
        );
    }

    private function buildPendingRolesResponse()
    {
        if ($this->useEmptyPendingRolesResponse) {
            return [];
        }

        $response = [
            'system' => [],
            'organisations' => [],
            'sites' => [],
        ];

        foreach ($this->pendingOrganisationRoles as $pendingRoleTuple) {
            $orgId = $pendingRoleTuple[0];
            $roleCode = $pendingRoleTuple[1];

            if (!isset($response['organisations'][$orgId])) {
                $organisation = [
                    'name' => 'Test Organisation',
                    'number' => 'B000058',
                    'address' => 'Flat 57972a7fca2616.00930308 Lord House, Ipswich, IP1 1LL',
                    'roles' => [],
                ];

                $response['organisations'][$orgId] = $organisation;
            }

            $response['organisations'][$orgId]['roles'][] = $roleCode;
        }

        foreach ($this->pendingSiteRoles as $pendingSiteTuple) {
            $siteId = $pendingSiteTuple[0];
            $roleCode = $pendingSiteTuple[1];

            if (!isset($response['sites'][$siteId])) {
                $site = [
                    'name' => 'Test Garage',
                    'address' => 'addressLine1, Toulouse, BS1 3LL',
                    'addressParts' => [],
                    'roles' => [],
                ];

                $response['sites'][$siteId] = $site;
            }

            $response['sites'][$siteId]['roles'][] = $roleCode;
        }

        return $response;
    }
}
