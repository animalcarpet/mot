<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class SiteRepositoryTest.
 */
class VehicleTestingStationRepositoryTest extends AbstractServiceTestCase
{
    /**
     * Test the populated Vehicle Classes (not from the search).
     */
    public function testGetVehicleClasses()
    {
        $vehicleClasses = $this->getVtsValues('classes');

        $this->assertCount(6, $vehicleClasses);

        $this->assertArrayHasKey('1', $vehicleClasses);
        $this->assertArrayHasKey('2', $vehicleClasses);
        $this->assertArrayHasKey('3', $vehicleClasses);
        $this->assertArrayHasKey('4', $vehicleClasses);
        $this->assertArrayHasKey('5', $vehicleClasses);
        $this->assertArrayHasKey('7', $vehicleClasses);

        $this->assertEquals(VehicleClassCode::CLASS_1, $vehicleClasses['1']);
        $this->assertEquals(VehicleClassCode::CLASS_2, $vehicleClasses['2']);
        $this->assertEquals(VehicleClassCode::CLASS_3, $vehicleClasses['3']);
        $this->assertEquals(VehicleClassCode::CLASS_4, $vehicleClasses['4']);
        $this->assertEquals(VehicleClassCode::CLASS_5, $vehicleClasses['5']);
        $this->assertEquals(VehicleClassCode::CLASS_7, $vehicleClasses['7']);
    }

    /**
     * Test the populated Types array is exactly as expected.
     */
    public function testGetTypes()
    {
        $types = $this->getVtsValues('types');

        $this->assertCount(9, $types);

        $this->assertArrayHasKey('ctc', $types);
        $this->assertArrayHasKey('vts', $types);
        $this->assertArrayHasKey('vro', $types);
        $this->assertArrayHasKey('gvts', $types);
        $this->assertArrayHasKey('area_office', $types);
        $this->assertArrayHasKey('service_desk', $types);
        $this->assertArrayHasKey('welcombe_house', $types);
        $this->assertArrayHasKey('berkeley_house', $types);
        $this->assertArrayHasKey('course_venue', $types);

        $this->assertEquals(SiteRepository::TYPE_CTC, $types['ctc']);
        $this->assertEquals(SiteRepository::TYPE_VTS, $types['vts']);
        $this->assertEquals(SiteRepository::TYPE_VRO, $types['vro']);
        $this->assertEquals(SiteRepository::TYPE_GVTS, $types['gvts']);
        $this->assertEquals(SiteRepository::TYPE_AREA_OFFICE, $types['area_office']);
        $this->assertEquals(SiteRepository::TYPE_SERVICE_DESK, $types['service_desk']);
        $this->assertEquals(SiteRepository::TYPE_WELCOMBE_HOUSE, $types['welcombe_house']);
        $this->assertEquals(SiteRepository::TYPE_BERKELEY_HOUSE, $types['berkeley_house']);
        $this->assertEquals(SiteRepository::TYPE_COURSE_VENUE, $types['course_venue']);
    }

    /**
     * Test the populated Vehicle Classes (not from the search).
     */
    public function testStatuses()
    {
        $statuses = $this->getVtsValues('statuses');

        $this->assertCount(5, $statuses);

        $this->assertArrayHasKey('applied', $statuses);
        $this->assertArrayHasKey('approved', $statuses);
        $this->assertArrayHasKey('lapsed', $statuses);
        $this->assertArrayHasKey('rejected', $statuses);
        $this->assertArrayHasKey('retracted', $statuses);

        $this->assertEquals(SiteRepository::STATUS_APPLIED, $statuses['applied']);
        $this->assertEquals(SiteRepository::STATUS_APPROVED, $statuses['approved']);
        $this->assertEquals(SiteRepository::STATUS_LAPSED, $statuses['lapsed']);
        $this->assertEquals(SiteRepository::STATUS_REJECTED, $statuses['rejected']);
        $this->assertEquals(SiteRepository::STATUS_RETRACTED, $statuses['retracted']);
    }

    protected function getVtsValues($name = 'types')
    {
        $vtsRepository = new SiteRepository(
            $this->getMockEntityManager(),
            new ClassMetadata('VehicleTestingStation')
        );

        if ($name == 'types') {
            return $vtsRepository->getTypes();
        } elseif ($name == 'statuses') {
            return $vtsRepository->getStatuses();
        } elseif ($name == 'classes') {
            return $vtsRepository->getVehicleClasses();
        }

        throw new \Exception("Unknown Vts Values type {$name}");
    }
}
