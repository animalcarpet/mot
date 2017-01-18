<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvlaInfo\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NoResultException;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\MotTestRepository;
use IntegrationApi\DvlaInfo\Service\DvlaInfoMotHistoryService;

class DvlaInfoMotHistoryServiceTest extends AbstractServiceTestCase
{
    const VRM = 'INT001';
    const TEST_NUMBER = '123456789';
    const V5C_REFERENCE = 'ZXC123456';

    /**
     * @var DvlaInfoMotHistoryService
     */
    private $underTest;
    private $mockRepository;

    protected function setUp()
    {
        $this->mockRepository = $this->getMockWithDisabledConstructor(MotTestRepository::class);
        $this->underTest = new DvlaInfoMotHistoryService($this->mockRepository);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function test_givenTestNumberAndVehicleNotFound_throwAnException()
    {
        //given
        $this->mockRepository->expects($this->once())
            ->method('findTestByVehicleRegistrationAndTestNumber')
        ->willThrowException(new NoResultException());

        $this->mockRepository->expects($this->never())
            ->method('findHistoricalTestsForVehicle');

        //when
        $this->underTest->getMotTests(self::VRM, self::TEST_NUMBER, null);

        //then an exception is thrown
        $this->assertTrue(false, "NotFoundException should be thrown!");
    }

    public function test_givenTestNumberAndMotTestsFound_shouldReturnMotTests()
    {
        //given
        $vehicle = (new Vehicle())->setFirstUsedDate(new \DateTime());
        $motTest = (new MotTest())
            ->setStatus($this->createMotTestActiveStatus())
            ->setVehicle($vehicle);

        $this->mockRepository->expects($this->once())
            ->method('findTestByVehicleRegistrationAndTestNumber')
            ->will($this->returnValue($motTest));

        $motTests = $this->prepareMotTestsArray($vehicle);
        $this->mockRepository->expects($this->once())
            ->method('findTestsExcludingNonAuthoritativeTestsForVehicle')
            ->will($this->returnValue($motTests));

        //when
        $result = $this->underTest->getMotTests(self::VRM, self::TEST_NUMBER, null);

        //then
        $this->assertTrue(is_array($result));
        $this->assertEquals('1234567', $result[0]['testNumber']);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\ServiceException
     */
    public function test_givenV5cReferenceAndMotTestsFound_shouldThrowAnException()
    {
        //given

        //when
        $this->underTest->getMotTests(self::VRM, null, self::V5C_REFERENCE);

        //then
        $this->assertTrue(
            false, "This test is to be removed when code for finding mot tests by VRM and V5C reference is implemented."
        );
    }

    public function prepareMotTestsArray($vehicle)
    {
        $number = "1234567";
        $expiryDate = new \DateTime();
        $vts = new Site();

        $motTest = (new MotTest())
            ->setStatus($this->createMotTestActiveStatus())
            ->setNumber($number)
            ->setExpiryDate($expiryDate)
            ->setOdometerValue(666)
            ->setOdometerUnit(OdometerUnit::MILES)
            ->setVehicleTestingStation($vts)
            ->setVehicle($vehicle);

        return [$motTest];
    }

    private function createMotTestActiveStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn(MotTestStatusName::ACTIVE);

        return $status;
    }
}
