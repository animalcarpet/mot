<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaMotApi\Controller\MotTestReasonForRejectionController;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApiTest\Service\MotTestServiceTest;

/**
 * Class MotTestReasonForRejectionControllerTest.
 */
class MotTestReasonForRejectionControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $defectSentenceCaseConverter = new DefectSentenceCaseConverter();
        $this->controller = new MotTestReasonForRejectionController($defectSentenceCaseConverter);
        parent::setUp();
    }

    public function testCreateWithValidData()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = 1;
        $motTest = MotTestServiceTest::getTestMotTestEntity();

        $rfrId = 2;
        $comment = 'This is a test comment';
        $data = [
            'rfrId' => $rfrId,
            'comment' => $comment,
        ];
        $expectedData = ['data' => 10];

        //  --  mock    --
        $mockMotTestRfrService = $this->getMockMotTestService();
        $mockMotTestRfrService
            ->expects($this->once())
            ->method('getMotTest')
            ->willReturn($motTest);

        $this->getMockRfrService()->expects($this->once())
            ->method('addReasonForRejection')
            ->with($motTest, $data)
            ->will($this->returnValue(10));

        //  --  make request    --
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->request->setMethod('post');
        $this->request->getPost()->set('rfrId', $rfrId);
        $this->request->getPost()->set('comment', $comment);

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    /**
     * @expectedException     \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionCode 403
     */
    public function testCreateWithInvalidData()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = 1;
        $motTest = MotTestServiceTest::getTestMotTestEntity();

        $rfrId = 2;
        $comment = 'This isnt gonna work';
        $forbiddenMessage = 'Cannot add this Rfr';

        //  --  mock    --
        $mockMotTestRfrService = $this->getMockMotTestService();
        $mockMotTestRfrService
            ->expects($this->once())
            ->method('getMotTest')
            ->willReturn($motTest);

        $this->getMockRfrService()->expects($this->once())
            ->method('addReasonForRejection')
            ->will($this->throwException(new ForbiddenException($forbiddenMessage)));

        //  --  make requst --
        $this->routeMatch->setParam('id', $motTestNumber);
        $this->request->setMethod('post');
        $this->request->getPost()->set('rfrId', $rfrId);
        $this->request->getPost()->set('comment', $comment);

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //  --  check   --
        $this->assertResponseStatusAndResultHasError(
            $response,
            self::HTTP_ERR_FORBIDDEN,
            $result,
            $forbiddenMessage,
            ForbiddenException::ERROR_CODE_FORBIDDEN
        );
    }

    public function testGetRequest()
    {
        $motTestNumber = 1;
        $expectedData = [
            'data' => [
                'id'=> 666,
                'parentCategoryId' => 261,
                'description' => '',
                'defectBreadcrumb' => 'Motorcycle drive system > Chain guard',
                'advisoryText' => '',
                'inspectionManualReference' => '6.2.1c',
                'advisory' => true,
                'prs' => true,
                'failure' => false,
                '_class' => 'DvsaCommon\Dto\MotTesting\DefectDto',
            ]
        ];

        $motTest = MotTestServiceTest::getTestMotTestEntity();
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);
        $this->mockDefectSentenceCaseConverter($expectedData);
        $testItemSelectorMock = $this->mockTestItemSelector($expectedData);
        $reasonForRejectionMock = $this->mockReasonForRejection($expectedData, $testItemSelectorMock);
        $this->mockMotTestRfrService($motTest, $reasonForRejectionMock);

        $this->request->setMethod('get');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->routeMatch->setParam('motTestRfrId', $expectedData['data']['id']);

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    public function testDeleteOk()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = 1;
        $rfrId = 2;
        $expectedData = ['data' => 'successfully deleted Reason for Rejection'];

        //  --  mock    --
        $this->getMockRfrService()->expects($this->once())
            ->method('deleteReasonForRejectionById')
            ->with($motTestNumber)
            ->will($this->returnValue(true));

        //  --  make request --
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->routeMatch->setParam('rfr-id', $rfrId);
        $this->request->setMethod('delete');

        $result = $this->controller->dispatch($this->request);

        //  --  check   --
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    //TODO: test create with rfr id provided (which is technically update)

    private function getMockRfrService()
    {
        return $this->getMockServiceManagerClass(
            MotTestReasonForRejectionService::class,
            MotTestReasonForRejectionService::class
        );
    }

    /**
     * @param $expectedData
     */
    private function mockDefectSentenceCaseConverter($expectedData)
    {
        $defectSentenceCaseConverterMock = $this
            ->getMockBuilder(DefectSentenceCaseConverter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defectSentenceCaseConverterMock
            ->expects($this->any())
            ->method('getDefectDetailsForAddADefect')
            ->willReturn($expectedData['data']['description'] + $expectedData['data']['advisoryText']);
    }

    /**
     * @param $expectedData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTestItemSelector($expectedData)
    {
        $testItemSelectorMock = $this
            ->getMockBuilder(TestItemSelector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testItemSelectorMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn($expectedData['data']['parentCategoryId']);

        return $testItemSelectorMock;
    }

    /**
     * @param $expectedData
     * @param $testItemSelectorMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockReasonForRejection($expectedData, $testItemSelectorMock)
    {
        $reasonForRejectionMock = $this
            ->getMockBuilder(ReasonForRejection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getRfrId')
            ->willReturn($expectedData['data']['id']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getTestItemSelector')
            ->willReturn($testItemSelectorMock);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getTestItemSelectorName')
            ->willReturn($expectedData['data']['defectBreadcrumb']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getInspectionManualReference')
            ->willReturn($expectedData['data']['inspectionManualReference']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getIsAdvisory')
            ->willReturn($expectedData['data']['advisory']);

        $reasonForRejectionMock
            ->expects($this->any())
            ->method('getIsPrsFail')
            ->willReturn($expectedData['data']['prs']);

        return $reasonForRejectionMock;
    }

    /**
     * @param $motTest
     * @param $reasonForRejectionMock
     */
    private function mockMotTestRfrService($motTest, $reasonForRejectionMock)
    {
        $mockMotTestRfrService = $this->getMockMotTestService();

        $mockMotTestRfrService
            ->expects($this->any())
            ->method('getMotTest')
            ->willReturn($motTest);

        $this->getMockRfrService()->expects($this->once())
            ->method('getDefect')
            ->willReturn($reasonForRejectionMock);
    }
}
