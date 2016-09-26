<?php
namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Controller\MotTestReasonForRejectionController;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApiTest\Service\MotTestServiceTest;

/**
 * Class MotTestReasonForRejectionControllerTest
 */
class MotTestReasonForRejectionControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();
        $featureToggles
            ->method('isEnabled')
            ->willReturn(true);
        $defectSentenceCaseConverter = new DefectSentenceCaseConverter($featureToggles);
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
            'rfrId'   => $rfrId,
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

        $result   = $this->controller->dispatch($this->request);

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

        $result   = $this->controller->dispatch($this->request);
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

    public function testDeleteOk()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = 1;
        $rfrId = 2;
        $expectedData = ["data" => "successfully deleted Reason for Rejection"];

        //  --  mock    --
        $this->getMockRfrService()->expects($this->once())
            ->method('deleteReasonForRejectionById')
            ->with($motTestNumber)
            ->will($this->returnValue(true));

        //  --  make request --
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->routeMatch->setParam('rfr-id', $rfrId);
        $this->request->setMethod('delete');

        $result   = $this->controller->dispatch($this->request);

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
}
