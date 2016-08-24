<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SearchDefectsController;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\Bootstrap;

class SearchDefectsControllerTest extends AbstractFrontendControllerTestCase
{
    /**
     * @var MotTestDto | \PHPUnit_Framework_MockObject_MockObject
     */
    private $motTestMock;

    protected function setUp()
    {
        $this->motTestMock = $this
            ->getMockBuilder(MotTestDto::class)
            ->disableOriginalConstructor()
            ->getMock();

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);
        $this->motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $vehicleClassMock = $this->getMockBuilder(VehicleClassDto::class)->getMock();
        $vehicleClassMock->expects($this->any())
            ->method('getCode')
            ->willReturn(VehicleClassCode::CLASS_1);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $vehicleMock = $this->getMockBuilder(VehicleDto::class)->disableOriginalConstructor()->getMock();
        $vehicleMock->expects($this->any())->method('getFirstUsedDate')->willReturn('2004-01-02');

        $this->motTestMock->expects($this->any())->method('getVehicle')->willReturn($vehicleMock);

        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new SearchDefectsController()
        );

        parent::setUp();
    }

    public function testLoadIndex()
    {
        $motTestNumber = 1;

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->at(0))
            ->method('get')
            ->willReturn(['data' => $this->motTestMock]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
        ];

        $queryParams = [
            'q' => '',
            'p' => 0,
        ];

        $this->getResultForAction2('get', 'index', $routeParams, $queryParams);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testLoadIndexWithSearchTerms()
    {
        $motTestNumber = 1;

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->at(0))
            ->method('get')
            ->willReturn(['data' => $this->motTestMock]);

        $restClientMock->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getDefects()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
        ];

        $queryParams = [
            'q' => 'door',
            'p' => 0,
        ];

        $this->getResultForAction2('get', 'index', $routeParams, $queryParams);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @return array
     */
    private function getDefects()
    {
        return [
            'reasonsForRejection' => [
                [
                    'rfrId' => 1,
                    'testItemSelectorId' => 1,
                    'description' => 'asd',
                    'testItemSelectorName' => 'asda',
                    'advisoryText' => 'asdsad',
                    'inspectionManualReference' => 'asdsa',
                    'testItemSelector' => 'asd',
                    'isAdvisory' => false,
                    'isPrsFail' => true,
                ],
            ],
        ];
    }
}
