<?php

namespace SiteApiTest\Service\Mapper;

use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Date\Time;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Entity\SiteFacility;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\VehicleClass;
use SiteApi\Service\Mapper\VtsMapper;

/**
 * Test functionality of VtsMapper class.
 */
class VtsMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var VtsMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new VtsMapper();

        parent::setUp();
    }

    public function testToDto()
    {
        //  --  test fully filled site  --
        $this->assertInstanceOf(VehicleTestingStationDto::class, $this->mapper->toDto(self::getVtsEntity()));

        //  --  test not contacts, not comments, no assessment, no organisation --
        $site = new Site();
        $site
            ->setId(99999)
            ->setSiteNumber('V99999')
            ->setName('UnitTestVts')
            ->setType((new SiteType())->setCode(SiteTypeCode::VEHICLE_TESTING_STATION));

        $this->assertInstanceOf(VehicleTestingStationDto::class, $this->mapper->toDto($site));
    }

    public function testManyToDto()
    {
        $result = $this->mapper->manyToDto([self::getVtsEntity()]);
        $this->assertInstanceOf(VehicleTestingStationDto::class, $result[0]);
    }

    public static function getVtsEntity()
    {
        //  --  Auth    --
        $authForMot1 = new AuthorisationForTestingMotAtSite();
        $authForMot1->setVehicleClass(
            (new VehicleClass())->setCode('code 1')
        );

        $authForMot2 = new AuthorisationForTestingMotAtSite();
        $authForMot2->setVehicleClass(
            (new VehicleClass())->setCode('code 2')
        );

        //  --  facilities  --
        $facility1 = new SiteFacility();
        $facility1
            ->setFacilityType(
                (new FacilityType())
                    ->setCode(FacilityTypeCode::AUTOMATED_TEST_LANE)
            );

        //  --  schedule   --
        $schedule1 = new SiteTestingDailySchedule();
        $schedule1
            ->setOpenTime(new Time(01, 02, 03))
            ->setCloseTime(new Time(23, 58, 59));

        //  --  roles   --
        $position1 = new SiteBusinessRoleMap();
        $position1
            ->setValidFrom(new \DateTime())
            ->setExpiryDate(new \DateTime())
            ->setStatusChangedOn(new \DateTime())
            ->setPerson(new Person())
            ->setBusinessRoleStatus(new BusinessRoleStatus())
            ->setSiteBusinessRole(new SiteBusinessRole());

        //  --  assemble    --
        /** @var $site */
        $site = SiteMapperTest::getSiteEntity();
        $site
            ->addAuthorisationsForTestingMotAtSite($authForMot1)
            ->addAuthorisationsForTestingMotAtSite($authForMot2)

            ->setDefaultBrakeTestClass1And2(new BrakeTestType())
            ->setDefaultParkingBrakeTestClass3AndAbove(new BrakeTestType())
            ->setDefaultServiceBrakeTestClass3AndAbove(new BrakeTestType())

            ->setFacilities([$facility1])

            ->setSiteTestingSchedule([$schedule1])

            ->setPositions([$position1]);

        return $site;
    }
}
