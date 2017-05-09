<?php

namespace UserApiTest\SpecialNotice\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use UserApi\SpecialNotice\Controller\SpecialNoticeBroadcastController;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\Http\Request;

/**
 * Class SpecialNoticeBroadcastControllerTest.
 */
class SpecialNoticeBroadcastControllerTest extends AbstractRestfulControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new SpecialNoticeBroadcastController();
        parent::setUp();
    }

    public function testPostGivenValidParamsReturns200Ok()
    {
        // given
        $userName = 'sn-cron-job';
        $this->mockValidAuthorization();

        $this->request->getHeaders()->addHeaders(['username' => $userName]);
        $this->request->setMethod('post');

        $mockSpecialNoticeService = $this->getMockServiceManagerClass(
            SpecialNoticeService::class,
            SpecialNoticeService::class
        );
        $mockSpecialNoticeService->expects($this->once())
            ->method('addNewSpecialNotices');

        // when
        $this->controller->dispatch($this->request);

        /** @var $response ApiResponse */
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetGivenInvalidVerbReturns405Error()
    {
        // given
        $userName = 'sn-cron-job';
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
            ['password' => 'Password1'],
            ['Authorization' => 'Bearer sn-cron-job-token']
        );
        $this->request->setMethod('post');

        $mockSpecialNoticeService = $this->getMockServiceManagerClass(
            SpecialNoticeService::class,
            SpecialNoticeService::class
        );

        $mockSpecialNoticeService->expects($this->once())
            ->method('addNewSpecialNotices')
            ->will($this->throwException(new ForbiddenException('SpecialNoticeBroadcast')));

        // when
        $result = $this->controller->dispatch($this->request);

        /** @var $response ApiResponse */
        $response = $this->controller->getResponse();
    }
}
