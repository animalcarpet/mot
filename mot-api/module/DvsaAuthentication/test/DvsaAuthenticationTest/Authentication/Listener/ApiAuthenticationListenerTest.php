<?php

namespace DvsaAuthenticationTest\Authentication\Listener;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use DvsaAuthentication\Authentication\Listener\ApiAuthenticationListener;
use Zend\Authentication\Result;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Router\RouteMatch;
use Dvsa\Mot\AuditApi\Service\HistoryAuditService;

class ApiAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAuthService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockKdd069;

    public function setUp()
    {
        $this->mockLogger = $this->getMockBuilder('Zend\Log\Logger')->disableOriginalConstructor()->getMock();
        $this->mockAuthService = $this->getMockBuilder('Zend\Authentication\AuthenticationService')->getMock();
        $this->mockEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->disableOriginalConstructor()->getMock();
        $this->mockKdd069 = $this->getMockBuilder(HistoryAuditService::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * If the route failed to match then it will return false and do nothing
     * else.
     */
    public function testNoAuthCheckWhenNoRouteMatches()
    {
        $listener = new ApiAuthenticationListener($this->mockAuthService, $this->mockLogger, [], $this->mockKdd069);
        $this->assertFalse($listener($this->mockEvent));
    }

    /**
     * The listener will not check controllers that appear in the whitelist.
     */
    public function testNoAuthForControllerOnWhitelist()
    {
        $controllerName = 'NoAuth\Controller';

        // create the listener with the controller name in the whitelist
        $listener = new ApiAuthenticationListener($this->mockAuthService, $this->mockLogger, [$controllerName], $this->mockKdd069);

        $matches = new RouteMatch([]);
        $matches->setParam('controller', $controllerName);
        $this->mockEvent->expects($this->once())
            ->method('getRouteMatch')
            ->will($this->returnValue($matches));

        $this->assertFalse($listener($this->mockEvent));
    }

    public function testStatusCodeOfFailedAuthentication()
    {
        $listener = $this->getTestListener();
        $response = $listener($this->mockEvent);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testContentTypeIsJsonForBadAuth()
    {
        $listener = $this->getTestListener();
        $response = $listener($this->mockEvent);
        $contentType = $response->getHeaders()->get('Content-Type');

        $this->assertEquals('application/json', $contentType->getFieldValue());
    }

    /**
     * @return ApiAuthenticationListener
     */
    protected function getTestListener()
    {
        $this->mockEvent->expects($this->once())
            ->method('getRouteMatch')
            ->will($this->returnValue(new RouteMatch([])));

        $request = new Request();
        $request->setHeaders(new Headers());

        $this->mockEvent->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue(new Response()));

        $this->mockAuthService->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new Result(Result::FAILURE, false)));

        $listener = new ApiAuthenticationListener($this->mockAuthService, $this->mockLogger, [], $this->mockKdd069);

        return $listener;
    }
}
