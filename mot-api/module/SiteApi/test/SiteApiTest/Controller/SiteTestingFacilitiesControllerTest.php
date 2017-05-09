<?php

namespace SiteApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\SiteTestingFacilitiesController;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Service\SiteTestingFacilitiesService;

class SiteTestingFacilitiesControllerTest extends AbstractRestfulControllerTestCase
{
    /** @var SiteTestingFacilitiesService|MockObj */
    private $mockService;

    const SITE_ID = 1;
    const SITE_NR = 'V1234';

    public function setUp()
    {
        $this->mockService = XMock::of(SiteTestingFacilitiesService::class);
        $this->controller = new SiteTestingFacilitiesController(
            $this->mockService
        );
        parent::setUp();
        $this->setUpController($this->controller);
    }

    /**
     * Test if method is accessible for call with valid parameters.
     *
     * @param string $method        HTTP method
     * @param string $action        route action
     * @param array  $params        route parameters
     * @param array  $queryParams   query parameters
     * @param string $serviceMethod mocked service method
     * @param array  $serviceReturn service method will return
     * @param array  $expectResult  expected method result
     * @param array  $postParams
     *
     * @dataProvider dataProviderTestWithValidParam
     */
    public function testMethodCallWithValidParams(
        $method,
        $action,
        $params,
        $queryParams,
        $serviceMethod,
        $serviceReturn,
        $expectResult,
        $postParams = null
    ) {
        $this->mockValidAuthorization(array(Role::DVSA_AREA_OFFICE_1));
        $this->setupMockForCalls($this->mockService, $serviceMethod, $serviceReturn);

        $result = $this->getResultForAction($method, $action, $params, $queryParams, $postParams);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectResult, $result);
    }

    public function dataProviderTestWithValidParam()
    {
        $getSrvResult = (new VehicleTestingStationDto())
            ->setId(self::SITE_ID)
            ->setSiteNumber(self::SITE_NR);

        $getSrvResultDto = DtoHydrator::dtoToJson(new VehicleTestingStationDto());
        $getExpect = ['data' => $getSrvResultDto];

        $postSrvResult = ['id' => self::SITE_ID];
        $postExpect = ['data' => $postSrvResult];

        return [
            [
                'put',
                null,
                [
                    'id' => self::SITE_ID,
                    'data' => [
                        'test' => 'tmp',
                        '_class' => 'DvsaCommon\\Dto\\Site\\VehicleTestingStationDto',
                    ],
                ],
                [],
                'update',
                $postSrvResult,
                $postExpect,
            ],
        ];
    }
}
