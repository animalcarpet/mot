<?php

namespace SiteApiTest\Service;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use SiteApi\Service\DefaultBrakeTestsService;

/**
 * Tests for DefaultBrakeTestService.
 */
class DefaultBrakeTestsServiceTest extends AbstractServiceTestCase
{
    /** @var DefaultBrakeTestsService */
    private $defaultBrakeTestService;
    /** @var \PHPUnit_Framework_MockObject_MockObject|SiteRepository */
    private $siteRepositoryMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|BrakeTestTypeRepository */
    private $brakeTestTypeRepository;
    private $authServiceMock;
    private $vehicleTestingStationId = 1;

    public function setUp()
    {
        $this->siteRepositoryMock = $this->getMockWithDisabledConstructor(SiteRepository::class);
        $this->brakeTestTypeRepository = $this->getMockWithDisabledConstructor(BrakeTestTypeRepository::class);
        $this->authServiceMock = $this->getMockAuthorizationService();

        $this->defaultBrakeTestService = new DefaultBrakeTestsService(
            $this->siteRepositoryMock,
            $this->brakeTestTypeRepository,
            new BrakeTestConfigurationValidator(),
            $this->authServiceMock
        );
    }

    public function testPutWithNoValues()
    {
        $this->siteRepositoryMock
            ->expects($this->any())->method('get')->will($this->returnValue(self::createFakeSite()));

        $this->defaultBrakeTestService->put($this->vehicleTestingStationId, []);
    }

    public function testPutWithAllValues()
    {
        $site1 = self::createFakeSite();
        $brakeTestTypeFloor = BrakeTestTypeFactory::floor();
        $brakeTestTypePlate = BrakeTestTypeFactory::plate();
        $brakeTestTypeGradient = BrakeTestTypeFactory::gradient();

        $this->siteRepositoryMock->expects($this->any())->method('get')->will($this->returnValue($site1));
        $this->setupMockForBrakeTestTypeRepository($brakeTestTypeFloor, $brakeTestTypePlate, $brakeTestTypeGradient);

        $inputData = [
            'defaultBrakeTestClass1And2' => BrakeTestTypeCode::FLOOR,
            'defaultServiceBrakeTestClass3AndAbove' => BrakeTestTypeCode::PLATE,
            'defaultParkingBrakeTestClass3AndAbove' => BrakeTestTypeCode::GRADIENT,
        ];

        $this->defaultBrakeTestService->put($this->vehicleTestingStationId, $inputData);

        $this->assertEquals(BrakeTestTypeCode::FLOOR, $site1->getDefaultBrakeTestClass1And2()->getCode());
        $this->assertEquals(BrakeTestTypeCode::PLATE, $site1->getDefaultServiceBrakeTestClass3AndAbove()->getCode());
        $this->assertEquals(BrakeTestTypeCode::GRADIENT, $site1->getDefaultParkingBrakeTestClass3AndAbove()->getCode());
    }

    /**
     * @param BrakeTestType $floor
     * @param BrakeTestType $plate
     * @param BrakeTestType $gradient
     */
    private function setupMockForBrakeTestTypeRepository($floor, $plate, $gradient)
    {
        $this->brakeTestTypeRepository->expects($this->at(0))->method('getByCode')->will($this->returnValue($floor));
        $this->brakeTestTypeRepository->expects($this->at(1))->method('getByCode')->will($this->returnValue($gradient));
        $this->brakeTestTypeRepository->expects($this->at(2))->method('getByCode')->will($this->returnValue($plate));
    }

    /**
     * @return Site
     */
    private static function createFakeSite()
    {
        return (new Site())->setOrganisation(new Organisation());
    }
}
