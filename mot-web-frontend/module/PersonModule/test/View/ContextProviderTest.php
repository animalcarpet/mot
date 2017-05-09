<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\View;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as Router;

class ContextProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RouteMatch
     */
    private $routeMatch;

    /**
     * @var ContextProvider
     */
    private $contextProvider;

    public function setUp()
    {
        $this
            ->request = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->routeMatch = $this
            ->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->router = $this
            ->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this
            ->router
            ->method('match')
            ->willReturn($this->routeMatch);

        $this->contextProvider = new ContextProvider($this->router, $this->request);
    }

    /**
     * @param string $routeName
     * @param string $expectedContext
     *
     * @dataProvider routesProvider
     */
    public function testGetContext($routeName, $expectedContext)
    {
        $this
            ->routeMatch
            ->expects($this->once())
            ->method('getMatchedRouteName')
            ->willReturn($routeName);

        $this->assertEquals($expectedContext, $this->contextProvider->getContext());
    }

    /**
     * @return array
     */
    public function routesProvider()
    {
        return [
            ['', ContextProvider::NO_CONTEXT],
            ['unknownParentRoute', ContextProvider::NO_CONTEXT],
            ['unknownParentRoute/', ContextProvider::NO_CONTEXT],
            ['unknownParentRoute/newProfile', ContextProvider::NO_CONTEXT],
            [ContextProvider::YOUR_PROFILE_PARENT_ROUTE, ContextProvider::YOUR_PROFILE_CONTEXT],
            [ContextProvider::YOUR_PROFILE_PARENT_ROUTE.'/', ContextProvider::YOUR_PROFILE_CONTEXT],
            [ContextProvider::YOUR_PROFILE_PARENT_ROUTE.'/someSubRoute', ContextProvider::YOUR_PROFILE_CONTEXT],
            [ContextProvider::AE_PARENT_ROUTE, ContextProvider::AE_CONTEXT],
            [ContextProvider::AE_PARENT_ROUTE.'/', ContextProvider::AE_CONTEXT],
            [ContextProvider::AE_PARENT_ROUTE.'/someSubRoute', ContextProvider::AE_CONTEXT],
            [ContextProvider::VTS_PARENT_ROUTE, ContextProvider::VTS_CONTEXT],
            [ContextProvider::VTS_PARENT_ROUTE.'/', ContextProvider::VTS_CONTEXT],
            [ContextProvider::VTS_PARENT_ROUTE.'/someSubRoute', ContextProvider::VTS_CONTEXT],
            [ContextProvider::USER_SEARCH_PARENT_ROUTE, ContextProvider::USER_SEARCH_CONTEXT],
            [ContextProvider::USER_SEARCH_PARENT_ROUTE.'/', ContextProvider::USER_SEARCH_CONTEXT],
            [ContextProvider::USER_SEARCH_PARENT_ROUTE.'/someSubRoute', ContextProvider::USER_SEARCH_CONTEXT],
        ];
    }
}
