<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaMotApi\Controller\TesterExpiryController;
use DvsaMotApi\Service\TesterExpiryService;
use Zend\Http\Request;

/**
 * Class TesterExpiryControllerTest.
 */
class TesterExpiryControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new TesterExpiryController();
        parent::setUp();
    }

    public function testPostGivenValidParamsReturns200Ok()
    {
        // given
        $userName = 'tester-expiry-cron-job';
        $this->mockValidAuthorization();

        $this->request->getHeaders()->addHeaders(['username' => $userName]);
        $this->request->setMethod('post');

        $mockTesterExpiryService = $this->getMockServiceManagerClass(
            'TesterExpiryService', TesterExpiryService::class
        );
        $mockTesterExpiryService->expects($this->once())
            ->method('changeStatusOfInactiveTesters');

        // when
        $result = $this->controller->dispatch($this->request);

        /** @var $response ApiResponse */
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetGivenInvalidVerbReturns405Error()
    {
        // given
        $userName = 'tester-expiry-cron-job';
        $this->routeMatch->setParam('username', $userName);
        // when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // then
        $this->assertResponse405Error($response, $result);
    }

    /**
     * @expectedException     \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionCode 403
     */
    public function testCreateGivenInvalidAuthResultsIn403Error()
    {
        // given
        $userName = 'tester1';
        $this->request->getHeaders()->addHeaders(
            ['username' => $userName],
            ['password' => 'Password1'], ['Authorization' => 'Bearer tester-expiry-job-token']
        );
        $this->request->setMethod('post');

        $mockTesterExpiryService = $this->getMockServiceManagerClass(
            'TesterExpiryService', TesterExpiryService::class
        );

        $mockTesterExpiryService->expects($this->once())
            ->method('changeStatusOfInactiveTesters')
            ->will($this->throwException(new ForbiddenException('TesterExpiry')));

        // when
        $result = $this->controller->dispatch($this->request);

        /** @var $response ApiResponse */
        $response = $this->controller->getResponse();
    }
}
