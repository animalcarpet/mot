<?php

namespace ApplicationTest\Model;

use Application\Model\RoleSummaryCollection;
use DvsaCommon\Enum\RoleCode;
use PHPUnit_Framework_TestCase;

class RoleSummaryCollectionTest extends PHPUnit_Framework_TestCase
{
    private $organisationRoles;

    private $siteRoles;

    public function setUp()
    {
        $this->withNoRoles();
    }

    public function testCollectionIsEmptyWhenNoRolesSupplied()
    {
        $this->withNoRoles();

        $collection = new RoleSummaryCollection($this->buildRolesData());

        $this->assertTrue($collection->isEmpty());
    }

    public function testCollectionIsNotEmptyWhenRolesSupplied()
    {
        $this->withOrganisationRole(1, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);

        $collection = new RoleSummaryCollection($this->buildRolesData());

        $this->assertFalse($collection->isEmpty());
    }

    public function testCollectionHasOrganisationRoleWhenMatchingOrganisationRoleSupplied()
    {
        $this->withOrganisationRole(1, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);

        $collection = new RoleSummaryCollection($this->buildRolesData());

        $this->assertTrue($collection->containsOrganisationRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER));
        $this->assertFalse($collection->containsOrganisationRole(RoleCode::AUTHORISED_EXAMINER_DELEGATE));
        $this->assertFalse($collection->containsSiteRole(RoleCode::TESTER));
    }

    public function testCollectionHasSiteRoleWhenMatchingSiteRoleSupplied()
    {
        $this->withSiteRole(1, RoleCode::TESTER);

        $collection = new RoleSummaryCollection($this->buildRolesData());

        $this->assertTrue($collection->containsSiteRole(RoleCode::TESTER));
        $this->assertFalse($collection->containsSiteRole(RoleCode::SITE_ADMIN));
        $this->assertFalse($collection->containsOrganisationRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER));
    }

    public function testCollectionHasCombinationOfRolesWhenCombinationOfRolesSupplied()
    {
        $this
            ->withSiteRole(1, RoleCode::TESTER)
            ->withSiteRole(2, RoleCode::SITE_ADMIN)
            ->withOrganisationRole(1, RoleCode::AUTHORISED_EXAMINER_DELEGATE);

        $collection = new RoleSummaryCollection($this->buildRolesData());

        $this->assertTrue($collection->containsSiteRole(RoleCode::TESTER));
        $this->assertTrue($collection->containsSiteRole(RoleCode::SITE_ADMIN));
        $this->assertTrue($collection->containsOrganisationRole(RoleCode::AUTHORISED_EXAMINER_DELEGATE));
        $this->assertFalse($collection->containsOrganisationRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER));
        $this->assertFalse($collection->containsSiteRole(RoleCode::SITE_MANAGER));
    }

    private function withOrganisationRole($organisationId, $roleCode)
    {
        $this->organisationRoles[] = [$organisationId, $roleCode];

        return $this;
    }

    private function withSiteRole($siteId, $roleCode)
    {
        $this->siteRoles[] = [$siteId, $roleCode];

        return $this;
    }

    private function withNoRoles()
    {
        $this->organisationRoles = [];
        $this->siteRoles = [];

        return $this;
    }

    private function buildRolesData()
    {
        $response = [
            'system' => [],
            'organisations' => [],
            'sites' => [],
        ];

        foreach ($this->organisationRoles as $pendingRoleTuple) {
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

        foreach ($this->siteRoles as $pendingSiteTuple) {
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
