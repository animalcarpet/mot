<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack as Router;
use Zend\Mvc\Router\Http\RouteMatch;
use PHPUnit_Framework_TestCase;
use Dvsa\Mot\Frontend\MotTestModule\Module;

class DefectsJourneyContextProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request | \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var Router | \PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var RouteMatch | \PHPUnit_Framework_MockObject_MockObject
     */
    private $routeMatch;

    /**
     * @var DefectsJourneyContextProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextProvider;

    public function setUp()
    {
        $this->request = $this
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

        $this->contextProvider = new DefectsJourneyContextProvider($this->router, $this->request);
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
            [ '', DefectsJourneyContextProvider::NO_CONTEXT ],
            [ 'unknownParentRoute', DefectsJourneyContextProvider::NO_CONTEXT ],
            [ 'unknownParentRoute/', DefectsJourneyContextProvider::NO_CONTEXT ],
            [ 'unknownParentRoute/unknownRoute', DefectsJourneyContextProvider::NO_CONTEXT ],

            [ DefectsJourneyContextProvider::MOT_TEST_RESULTS_PARENT_ROUTE, DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ DefectsJourneyContextProvider::MOT_TEST_RESULTS_PARENT_ROUTE . '/unknownRoute', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ DefectsJourneyContextProvider::MOT_TEST_RESULTS_PARENT_ROUTE . '/edit-defect', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ DefectsJourneyContextProvider::MOT_TEST_RESULTS_PARENT_ROUTE . '/remove-defect', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],

            [ Module::TOP_LEVEL_ROUTE, DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/otherRoute', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/unknownRoute', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/unknownRoute/otherRoute', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/edit-defect', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/edit-defect/otherRoute', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/remove-defect', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],
            [ Module::TOP_LEVEL_ROUTE . '/remove-defect/otherRoute', DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT ],

            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE, DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/otherRoute', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/add-manual-advisory', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/add-manual-advisory/otherRoute', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/add-defect', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/add-defect/otherRoute', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/edit-defect', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/edit-defect/otherRoute', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/remove-defect', DefectsJourneyContextProvider::SEARCH_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE . '/remove-defect/otherRoute', DefectsJourneyContextProvider::SEARCH_CONTEXT ],

            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE, DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/', DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/otherRoute', DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/otherRoute', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/add-manual-advisory', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/add-manual-advisory/otherRoute', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/add-defect', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/add-defect/otherRoute', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/edit-defect', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/edit-defect/otherRoute', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/remove-defect', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
            [ DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE . '/' . DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE . '/category/remove-defect/otherRoute', DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT ],
        ];
    }
}