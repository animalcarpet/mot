<?php

namespace SiteApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\SiteController;
use SiteApi\Service\SiteService;
use Zend\Stdlib\Parameters;

/**
 * Class SiteControllerTest.
 */
class SiteControllerTest extends AbstractRestfulControllerTestCase
{
    protected $vehicleTestingStationRepository;
    protected $vehicleTestingStationHydrator;

    const SITE_ID = 1;
    const SITE_NR = 'V1234';

    /** @var SiteService|MockObj */
    private $mockService;

    protected function setUp()
    {
        $this->mockService = XMock::of(SiteService::class);
        $this->setController(new SiteController($this->mockService));

        parent::setUp();

        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = $this->getController()->getServiceLocator();
    }

    /**
     * Test method is accessible for call with valid parameters.
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
    public function testWithValidParam(
        $method,
        $action,
        $params,
        $queryParams,
        $serviceMethod,
        $serviceReturn,
        $expectResult,
        $postParams = null
    ) {
        $this->mockValidAuthorization(array(Role::VEHICLE_EXAMINER));

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
                'get',
                null,
                ['id' => self::SITE_ID],
                ['dto' => true],
                'getSite',
                $getSrvResultDto,
                $getExpect,
            ],
            [
                'post',
                null,
                ['data' => []],
                [],
                'create',
                $postSrvResult,
                $postExpect,
                ['_class' => 'DvsaCommon\\Dto\\Site\\VehicleTestingStationDto'],
            ],
        ];
    }

    /**
     * Test service method return errors when call with invalid parameters.
     *
     * @param string $method        HTTP method
     * @param string $action        route action
     * @param string $serviceMethod mocked service method
     * @param string $paramName     set value for parameter with name
     *
     * @dataProvider dataProviderTestWithInvalidParams
     */
    public function testWithInvalidParams($method, $action, $serviceMethod, $paramName)
    {
        $this->mockValidAuthorization(array(Role::VEHICLE_EXAMINER));

        $paramValue = 'notExists';

        $exception = new NotFoundException('Site', $paramValue);
        $this->setupMockForCalls($this->mockService, $serviceMethod, $exception, $paramValue);

        $this->setExpectedException(
            NotFoundException::class,
            sprintf(NotFoundException::ERROR_MSG_NOT_FOUND, 'Site', ' '.$paramValue),
            NotFoundException::ERROR_CODE_NOT_FOUND
        );

        $this->getResultForAction($method, $action, [$paramName => $paramValue]);
    }

    public function dataProviderTestWithInvalidParams()
    {
        return [
            [
                'method' => 'get',
                'action' => null,
                'serviceMethod' => 'getSite',
                'paramName' => 'id',
            ],
        ];
    }

    protected function getQueryTestResponse($siteData)
    {
        $response = $this->getTestResponse($siteData);
        $response['data']['resultCount'] = 1;

        return $response;
    }

    protected function getTestResponse($siteData)
    {
        return [
            'data' => [
                'vehicleTestingStation' => $siteData,
            ],
        ];
    }
}
