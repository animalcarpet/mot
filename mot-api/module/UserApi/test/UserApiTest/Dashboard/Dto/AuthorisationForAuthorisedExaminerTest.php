<?php

namespace UserApiTest\Dashboard\Dto;

use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer as AuthorisationForAuthorisedExaminerEntity;
use DvsaEntities\Entity\Organisation;
use UserApi\Dashboard\Dto\AuthorisationForAuthorisedExaminer;
use UserApi\Dashboard\Dto\Site;

/**
 * Unit tests for AuthorisationForAuthorisedExaminer dto.
 */
class AuthorisationForAuthorisedExaminerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function test_toArray_noSites_shouldReturnBasicStructure()
    {
        $this->runTest_toArray_siteList([]);
    }

    public function test_toArray_oneSite_shouldReturnBasicStructureWithOneSite()
    {
        $this->runTest_toArray_siteList([$this->vts()]);
    }

    public function test_toArray_manySites_shouldReturnBasicStructureWithManySite()
    {
        $this->runTest_toArray_siteList([$this->vts(), $this->vts(), $this->vts(), $this->vts()]);
    }

    private function runTest_toArray_siteList($sites)
    {
        $aeEntity = new AuthorisationForAuthorisedExaminerEntity();
        $aeEntity->setOrganisation(new Organisation());

        $ae = new AuthorisationForAuthorisedExaminer($aeEntity, 1, $sites, 'AED', 1);

        $result = $ae->toArray();
        $this->assertWellFormedData($result);
        $this->assertCount(count($sites), $result['sites']);
    }

    private function vts()
    {
        return new Site(SiteTest::createVehicleTestingStationEntityWithPositions(), []);
    }

    private function assertWellFormedData($data)
    {
        return is_array($data)
            && isset($data['id'])
            && isset($data['reference'])
            && isset($data['name'])
            && isset($data['tradingAs'])
            && isset($data['managerId'])
            && isset($data['slots'])
            && isset($data['slotsWarnings'])
            && isset($data['sites'])
            && is_array($data['sites'])
            && isset($data['position'])
        ;
    }
}
