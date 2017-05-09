<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\EnforcementMotTestResultController;
use DvsaMotApi\Service\EnforcementMotTestResultService;

/**
 * Class EnforcementMotTestResultControllerTest.
 */
class EnforcementMotTestResultControllerTest extends AbstractMotApiControllerTestCase
{
    // Dummy ID for route matches
    const TEST_ID = 1;

    protected function setUp()
    {
        $this->setController(new EnforcementMotTestResultController());
        parent::setUp();
    }

    public function testGetCanBeAccessed()
    {
        $this->mockValidAuthorization(array(Role::VEHICLE_EXAMINER));

        $mockService = $this->getMockMotTestResultService();
        $mockService->expects($this->once())
            ->method('getEnforcementMotTestResultData')
            ->will($this->returnValue(array()));

        $this->routeMatch->setParam('id', self::TEST_ID);
        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => []], $result);
    }

    public function testPostCanBeAccessed()
    {
        $this->mockValidAuthorization(array(Role::VEHICLE_EXAMINER));

        $mockService = $this->getMockMotTestResultService();
        $mockService->expects($this->once())
            ->method('createEnforcementMotTestResult')
            ->will($this->returnValue(array()));

        $this->request->setMethod('post');
        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => []], $result);
    }

    public function testGetService()
    {
        $this->assertEquals(
            $this->getMockMotTestResultService(),
            XMock::invokeMethod($this->getController(), 'getService')
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\UnauthenticatedException
     */
    public function testPutServiceFailsWhenNotAuthorised()
    {
        $this->routeMatch->setParam('id', self::TEST_ID);
        $this->request->setMethod('put');
        $this->controller->dispatch($this->request);
    }

    public function testPutServiceRunsWhenAuthorised()
    {
        $this->mockValidAuthorization(array(Role::VEHICLE_EXAMINER));

        $mockService = $this->getMockMotTestResultService();
        $mockService->expects($this->once())
            ->method('updateEnforcementMotTestResult')
            ->will($this->returnValue(array()));

        $this->routeMatch->setParam('id', self::TEST_ID);
        $this->request->setMethod('put');
        $this->controller->dispatch($this->request);
    }

    private function getMockMotTestResultService(array $methods = null)
    {
        $mockService = $this->getMockServiceManagerClass(
            'EnforcementMotTestResultService', EnforcementMotTestResultService::class, $methods
        );

        return $mockService;
    }
}
