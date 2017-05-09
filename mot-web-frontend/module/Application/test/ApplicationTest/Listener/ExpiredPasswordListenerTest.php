<?php

namespace ApplicationTest\Listener;

use Account\Service\ExpiredPasswordService;
use Application\Listener\ExpiredPasswordListener;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use Zend\Http\Response;
use Zend\Log\LoggerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;

class ExpiredPasswordListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var DateTimeHolder
     */
    private $timeHolder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExpiredPasswordService
     */
    private $expiredPasswordService;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var TreeRouteStack
     */
    private $router;

    public function setUp()
    {
        $this->identityProvider = $this
            ->getMockBuilder(MotIdentityProviderInterface::class)
            ->getMock();

        $this->timeHolder = $this
            ->getMockBuilder(DateTimeHolder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timeHolder
            ->method('getCurrent')
            ->will(
                $this->returnValue(
                    new \DateTime('2016-01-01')
                )
            );

        $this->logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->expiredPasswordService = $this
            ->getMockBuilder(ExpiredPasswordService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expiredPasswordService
            ->method('calculatePasswordChangePromptDate')
            ->will($this->returnValue(new \DateTime('2000-01-01')));

        $this->router = $this
            ->getMockBuilder(TreeRouteStack::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test that the URL that the user is redirected to when their password
     * expires is the correct one.
     *
     * @coversNothing
     */
    public function testChangePasswordLinkIsCorrectNewProfile()
    {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('newProfile');

        $expiredPasswordListener = new ExpiredPasswordListener(
            $this->identityProvider,
            $this->timeHolder,
            $this->logger,
            $this->expiredPasswordService
        );

        $e = new MvcEvent();
        $e->setName(MvcEvent::EVENT_DISPATCH);
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRouter($this->router);

        $expiredPasswordListener($e);

        $this->assertEquals(
            Response::STATUS_CODE_200, $e->getResponse()->getStatusCode()
        );
    }

    /**
     * Test that the URL that the user is redirected to when their password
     * expires is the correct one.
     *
     * @coversNothing
     */
    public function testChangePasswordLinkIsCorrectOldProfile()
    {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('newProfile');

        $expiredPasswordListener = new ExpiredPasswordListener(
            $this->identityProvider,
            $this->timeHolder,
            $this->logger,
            $this->expiredPasswordService
        );

        $e = new MvcEvent();
        $e->setName(MvcEvent::EVENT_DISPATCH);
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRouter($this->router);

        $expiredPasswordListener($e);

        $this->assertEquals(
            Response::STATUS_CODE_200, $e->getResponse()->getStatusCode()
        );
    }
}
