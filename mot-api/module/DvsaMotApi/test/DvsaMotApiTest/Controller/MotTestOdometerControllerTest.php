<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaMotApi\Controller\MotTestOdometerController;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\OdometerReadingQueryService;
use DvsaMotApi\Service\OdometerReadingUpdatingService;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;

/**
 * Class MotTestOdometerControllerTest.
 */
class MotTestOdometerControllerTest extends AbstractMotApiControllerTestCase
{
    public function testCreateGivenInputShouldPassCorrectReadingDownToService()
    {
        $motTest = new MotTest();
        $motTestNumber = 1;
        $odometerValue = '100';
        $odometerUnit = OdometerUnit::MILES;
        $odometerResultType = OdometerReadingResultType::OK;
        $this->request->setMethod('PUT');

        $content = ['value' => $odometerValue,
                    'unit' => $odometerUnit,
                    'resultType' => $odometerResultType, ];
        $this->request->setContent(json_encode($content));
        $this->request->getHeaders()->addHeader(ContentType::fromString('content-type: application/json'));
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $readingCapture = ArgCapture::create();
        $motTestRepository = $this->getMockServiceManagerClass(MotTestRepository::class, MotTestRepository::class);
        /** @var OdometerReadingUpdatingService $updateService */
        $updateService = $this->getMockServiceManagerClass(
            'OdometerReadingUpdatingService',
            OdometerReadingUpdatingService::class
        );
        $updateService->expects($this->any())->method('updateForMotTest')->with($readingCapture(), $this->anything());
        $motTestRepository->expects($this->any())->method('getMotTestByNumber')->will($this->returnValue($motTest));

        $this->controller->dispatch($this->request);

        $this->assertThat(
            $readingCapture->get(),
            $this->logicalAnd(
                $this->attributeEqualTo('unit', $odometerUnit),
                $this->attributeEqualTo('value', $odometerValue),
                $this->attributeEqualTo('resultType', $odometerResultType)
            ),
            'Passed OdometerReading is invalid'
        );
    }

    /**
     * Represents potential results of the service method returning
     * information on whether it is possible to update odometer reading
     * of the specified MOT test.
     *
     * @return array
     */
    public static function dataTestCanModifyOdometerActionChecks()
    {
        return [[true, "Odometer should be modifiable but it isn't"],
                [false, 'Odometer should not be modifiable but it is'], ];
    }

    /**
     * @dataProvider dataTestCanModifyOdometerActionChecks
     */
    public function testCanModifyOdometerActionGivenCheckResultShouldReturnAccordingly($checkResult, $message)
    {
        $motTest = new MotTest();
        $motTestNumber = 1;
        $this->request->setMethod('GET');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->routeMatch->setParam('action', 'canModifyOdometer');
        $motTestRepository = $this->getMockServiceManagerClass('MotTestRepository', MotTestRepository::class);
        /** @var OdometerReadingUpdatingService $updateService */
        $updateService = $this->getMockServiceManagerClass(
            'MotTestSecurityService',
            MotTestSecurityService::class
        );
        $updateService->expects($this->any())->method('canModifyOdometerForTest')
            ->will($this->returnValue($checkResult));
        $motTestRepository->expects($this->any())->method('getMotTestByNumber')->will($this->returnValue($motTest));

        $result = $this->controller->dispatch($this->request);

        $this->assertEquals(
            $checkResult, $result->getVariables()['data']['modifiable'], $message
        );
    }

    public function testGetNoticesGivenInputShouldReturnServiceResultAsJson()
    {
        $motTest = new MotTest();
        $motTestNumber = 1;
        $serviceResult = ['example'];
        $expectedControllerResult = ApiResponse::jsonOk($serviceResult);
        $this->request->setMethod('POST');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->routeMatch->setParam('action', 'getNotices');
        $motTestRepository = $this->getMockServiceManagerClass('MotTestRepository', MotTestRepository::class);
        /** @var OdometerReadingQueryService $queryService */
        $queryService = $this->getMockServiceManagerClass(
            'OdometerReadingQueryService',
            OdometerReadingQueryService::class
        );
        $queryService->expects($this->any())->method('getNotices')
            ->will($this->returnValue($serviceResult));
        $motTestRepository->expects($this->any())->method('getMotTestByNumber')->will($this->returnValue($motTest));

        $controllerResult = $this->controller->dispatch($this->request);

        $this->assertEquals(
            $expectedControllerResult->getVariables(), $controllerResult->getVariables(),
            'Invalid JsonModel returned : '
            .var_export($controllerResult->getVariables(), true)
            .'when expected: '
            .var_export($expectedControllerResult->getVariables(), true)
        );
    }

    protected function setUp()
    {
        $this->controller = new MotTestOdometerController();
        TestTransactionExecutor::inject($this->controller);
        parent::setUp();
    }
}
