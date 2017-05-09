<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Controller\VehicleController;
use VehicleApi\Service\VehicleSearchService;
use Zend\Stdlib\Parameters;

/**
 * Class VehicleControllerTest.
 */
class VehicleControllerTest extends AbstractMotApiControllerTestCase
{
    const VEHICLE_TEST_ID = '1';
    const TEST_REG_MARK = 'CRZ 4545';
    const TEST_REG_MARK_SANITIZED = 'CRZ 4545';
    const TEST_SHORT_VIN = '111111';
    const TEST_FULL_VIN = '100000000001111111';
    const TEST_INCORRECT_VIN = '100000111';
    const TEST_NO_VIN = null;
    const TEST_NO_REG = null;
    const TEST_VIN_IS_PARTIAL = false;
    const TEST_VIN_IS_FULL = true;

    protected function setUp()
    {
        $this->controller = new VehicleController();
        parent::setUp();
    }

    public function testGetListWithPartialVinAndRegCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedVehiclesData = ['id' => 1];
        $expectedSearchReturn = [[$expectedVehiclesData], true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_SHORT_VIN, self::TEST_REG_MARK_SANITIZED, self::TEST_VIN_IS_PARTIAL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::VIN_QUERY_PARAMETER => self::TEST_SHORT_VIN,
                      VehicleController::REG_QUERY_PARAMETER => self::TEST_REG_MARK,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::PARTIAL_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTestResponse($expectedVehiclesData),
            $result
        );
    }

    public function testGetListWithPartialVinAndRegCanBeAccessedExplicitParameter()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedVehiclesData = ['id' => 1];
        $expectedSearchReturn = [[$expectedVehiclesData], true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_SHORT_VIN, self::TEST_REG_MARK_SANITIZED, self::TEST_VIN_IS_PARTIAL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::VIN_QUERY_PARAMETER => self::TEST_SHORT_VIN,
                      VehicleController::REG_QUERY_PARAMETER => self::TEST_REG_MARK,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::PARTIAL_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTestResponse($expectedVehiclesData),
            $result
        );
    }

    public function testGetListWithPartialVinAndRegManyResultsCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $twoVehiclesData = $this->getTwoVehiclesData();
        $expectedSearchReturn = [$twoVehiclesData, true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_SHORT_VIN, self::TEST_REG_MARK_SANITIZED, self::TEST_VIN_IS_PARTIAL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::VIN_QUERY_PARAMETER => self::TEST_SHORT_VIN,
                      VehicleController::REG_QUERY_PARAMETER => self::TEST_REG_MARK,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::PARTIAL_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTwoVehiclesTestResponse($twoVehiclesData),
            $result
        );
    }

    public function testGetListWithFullVinAndRegCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedVehiclesData = ['id' => 1];
        $expectedSearchReturn = [[$expectedVehiclesData], true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_FULL_VIN, self::TEST_REG_MARK_SANITIZED, self::TEST_VIN_IS_FULL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::VIN_QUERY_PARAMETER => self::TEST_FULL_VIN,
                      VehicleController::REG_QUERY_PARAMETER => self::TEST_REG_MARK,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::FULL_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTestResponse($expectedVehiclesData),
            $result
        );
    }

    public function testGetListWithFullVinAndNoRegCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedVehiclesData = ['id' => 1];
        $expectedSearchReturn = [[$expectedVehiclesData], true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_FULL_VIN, self::TEST_NO_REG, self::TEST_VIN_IS_FULL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::VIN_QUERY_PARAMETER => self::TEST_FULL_VIN,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::FULL_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTestResponse($expectedVehiclesData),
            $result
        );
    }

    public function testGetListWithNoVinTypeReturnsError()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResultHasError(
            $this->controller->getResponse(),
            self::HTTP_ERR_400,
            $result,
            VehicleController::VIN_TYPE_REQUIRED_MESSAGE,
            AbstractDvsaRestfulController::ERROR_CODE_REQUIRED
        );
    }

    public function testGetListWithNoVinAndRegCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedVehiclesData = ['id' => 1];
        $expectedSearchReturn = [[$expectedVehiclesData], true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_NO_VIN, self::TEST_REG_MARK_SANITIZED, self::TEST_VIN_IS_PARTIAL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::REG_QUERY_PARAMETER => self::TEST_REG_MARK,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::NO_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTestResponse($expectedVehiclesData),
            $result
        );
    }

    public function testGetListWithNoVinAndRegManyResultsCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $twoVehiclesData = [['id' => 1], ['id' => 2]];
        $expectedSearchReturn = [$twoVehiclesData, true];

        $mockVehicleService = $this->getMockServiceManagerClass(
            VehicleSearchService::class, VehicleSearchService::class
        );
        $mockVehicleService->expects($this->once())
            ->method('search')
            ->with(self::TEST_NO_VIN, self::TEST_REG_MARK_SANITIZED, self::TEST_VIN_IS_PARTIAL)
            ->will($this->returnValue($expectedSearchReturn));

        $this->request->setQuery(
            new Parameters(
                [VehicleController::REG_QUERY_PARAMETER => self::TEST_REG_MARK,
                      VehicleController::VIN_TYPE_PARAMETER => VehicleController::NO_VIN,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(
            self::HTTP_OK_CODE,
            $this->getQueryTwoVehiclesTestResponse($twoVehiclesData),
            $result
        );
    }

    protected function getTestResponse($vehicleData)
    {
        return [
            'data' => ['vehicle' => $vehicleData],
        ];
    }

    protected function getQueryTestResponse($vehicleData)
    {
        $response = $this->getTestResponse($vehicleData);
        $response['data']['resultType'] = VehicleController::SEARCH_RESULT_EXACT_MATCH;

        return $response;
    }

    protected function getQueryTwoVehiclesTestResponse($vehiclesData)
    {
        $response = [
            'data' => [
                'resultCount' => 2,
                'resultType' => VehicleController::SEARCH_RESULT_MULTIPLE_MATCHES,
                'vehicles' => $vehiclesData,
            ],
        ];

        return $response;
    }

    /**
     * @return array
     */
    private function getTwoVehiclesData()
    {
        $twoVehiclesData = [
            ['id' => 1,
             'registration' => 'RIA8080',
             'vin' => '4S4BP67CX45450432',
            ],
            ['id' => 2,
             'registration' => 'RIA8080',
             'vin' => 'S4BP67CX45450433',
            ],
        ];

        return $twoVehiclesData;
    }
}
