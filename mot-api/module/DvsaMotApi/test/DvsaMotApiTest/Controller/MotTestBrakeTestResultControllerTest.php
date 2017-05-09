<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaMotApi\Controller\MotTestBrakeTestResultController;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApiTest\Service\MotTestServiceTest;
use Zend\Http\Request;

/**
 * Class MotTestBrakeTestResultControllerTest.
 */
class MotTestBrakeTestResultControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new MotTestBrakeTestResultController();
        parent::setUp();
    }

    public function testCreateWithValidData()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = 1;
        $motTest = MotTestServiceTest::getTestMotTestEntity();
        //  $motTest->setId($motTestId);

        $formData = ['field1' => 'value1', 'field2' => 'value2'];

        //  --  mock objects --
        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService
            ->expects($this->once())
            ->method('getMotTest')
            ->with($motTestNumber)
            ->willReturn($motTest);

        $this->getBrakeTestResultService()
            ->expects($this->once())
            ->method('updateBrakeTestResult')
            ->with($motTest, $formData)
            ->willReturn(true);

        //  --  make request    --
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->request->setMethod('post');
        foreach ($formData as $fieldName => $value) {
            $this->request->getPost()->set($fieldName, $value);
        }

        $result = $this->controller->dispatch($this->request);

        //  --  check    --
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => []], $result);
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
        //  $motTest->setId($motTestId);

        $formData = ['incorrectField1' => 'value1', 'incorrectField2' => 'value2'];
        $forbiddenMessage = 'Invalid daaaataaaa';

        //  --  mock objects --
        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService
            ->expects($this->once())
            ->method('getMotTest')
            ->with($motTestNumber)
            ->willReturn($motTest);

        $this->getBrakeTestResultService()->expects($this->once())
            ->method('updateBrakeTestResult')
            ->with($motTest, $formData)
            ->will($this->throwException(new ForbiddenException($forbiddenMessage)));

        //  --  make request    --
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->request->setMethod('post');
        foreach ($formData as $fieldName => $value) {
            $this->request->getPost()->set($fieldName, $value);
        }

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //  --  check    --
        $this->assertResponseStatusAndResultHasError(
            $response,
            self::HTTP_ERR_FORBIDDEN,
            $result,
            $forbiddenMessage,
            ForbiddenException::ERROR_CODE_FORBIDDEN
        );
    }

    private function getBrakeTestResultService()
    {
        return $this->getMockServiceManagerClass('BrakeTestResultService', BrakeTestResultService::class);
    }
}
