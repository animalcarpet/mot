<?php

namespace UserApiTest\Dashboard\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Enum\SiteBusinessRoleCode as Role;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use UserApi\Dashboard\Dto\Site;

/**
 * Unit tests for Site dto.
 */
class SiteTest extends \PHPUnit_Framework_TestCase
{
    public function test_toArray_onePositionAtSite_shouldReturnCorrectArray()
    {
        $site = $this->createSiteWithPositions();
        $this->assertWellFormedData($site->toArray());
        $this->assertCount(0, $site->toArray()['positions']);
    }

    public function test_toArray_noPositionAtSite_shouldReturnCorrectArray()
    {
        $vts = new \DvsaEntities\Entity\Site();
        $site = new Site($vts, []);

        $this->assertWellFormedData($site->toArray());
    }

    public function test_toArray_manyPositionsAtSite_shouldReturnCorrectArray()
    {
        $site = $this->createSiteWithPositions(
            [
                1 => [Role::TESTER, Role::SITE_ADMIN, Role::SITE_MANAGER],
            ]
        );

        $this->assertWellFormedData($site->toArray());
        $this->assertCount(3, $site->toArray()['positions']);
        $this->assertEquals(Role::TESTER, $site->toArray()['positions'][0]);
        $this->assertEquals(Role::SITE_ADMIN, $site->toArray()['positions'][1]);
        $this->assertEquals(Role::SITE_MANAGER, $site->toArray()['positions'][2]);
    }

    public function test_getList_emptyArray_shouldReturnEmptyArray()
    {
        $this->assertCount(0, Site::getList([], 1));
    }

    public function test_getList_oneStation_shouldReturnArrayWithOneElement()
    {
        $personId = 1;
        $vts = self::createVehicleTestingStationEntityWithPositions(
            [
                $personId => [Role::TESTER],
            ]
        );

        $this->assertCount(1, Site::getList([$vts], $personId));
    }

    public function test_getList_manyStations_shouldReturnArrayWithManyElements()
    {
        $personId = 1;

        $vtsList = [
            self::createVehicleTestingStationEntityWithPositions([$personId => [Role::TESTER]]),
            self::createVehicleTestingStationEntityWithPositions([$personId => [Role::SITE_MANAGER]]),
            self::createVehicleTestingStationEntityWithPositions([$personId => [Role::SITE_ADMIN]]),
        ];

        $this->assertCount(3, Site::getList($vtsList, $personId));
    }

    private function createSiteWithPositions($positions = [])
    {
        $vts = self::createVehicleTestingStationEntityWithPositions($positions);

        return new Site($vts, $vts->getPositions());
    }

    public static function createVehicleTestingStationEntityWithPositions($positions = [])
    {
        $vts = new \DvsaEntities\Entity\Site();
        foreach ($positions as $workerId => $siteRoles) {
            foreach ($siteRoles as $siteRole) {
                self::addPositionToSite($vts, $siteRole, $workerId);
            }
        }

        return $vts;
    }

    private static function addPositionToSite(\DvsaEntities\Entity\Site $vts, $siteRole, $personId)
    {
        $person = new Person();
        $person->setId($personId);

        $positions = $vts->getPositions();

        if (!$positions) {
            $positions = new ArrayCollection();
        }
        $sr = new SiteBusinessRole();
        $sr->setCode($siteRole);
        $sbrm = new SiteBusinessRoleMap();
        $sbrm->setPerson($person);
        $sbrm->setSite($vts);
        $sbrm->setSiteBusinessRole($sr);
        $positions->add($sbrm);
        $vts->setPositions($positions);
    }

    private function assertWellFormedData($data)
    {
        return
            is_array($data)
            && isset($data['id'])
            && isset($data['name'])
            && isset($data['siteNumber'])
            && isset($data['positions'])
            && is_array($data['positions']);
    }
}
