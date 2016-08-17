<?php

namespace Dvsa\Mot\Frontend\MotTestModule\View;

use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack as Router;
use Zend\Mvc\Router\RouteMatch;
use Dvsa\Mot\Frontend\MotTestModule\Module;

/**
 * DefectsJourneyContextProvider.
 */
class DefectsJourneyContextProvider
{
    // contexts
    const SEARCH_CONTEXT = 'search';
    const BROWSE_CATEGORIES_CONTEXT = 'browse';
    const BROWSE_CATEGORIES_ROOT_CONTEXT = 'browse-root';
    const MOT_TEST_RESULTS_ENTRY_CONTEXT = 'mot-test-results';
    const NO_CONTEXT = 'none';
    // routes
    const SEARCH_PARENT_ROUTE = 'search';
    const BROWSE_CATEGORIES_PARENT_ROUTE = 'categories';
    const DEFECTS_TOP_LEVEL_ROUTE = Module::TOP_LEVEL_ROUTE;
    const MOT_TEST_RESULTS_PARENT_ROUTE = 'mot-test';

    const CATEGORY_ROUTE = 'category';
    const ADD_DEFECT_ROUTE = 'add-defect';
    const ADD_MANUAL_ADVISORY_ROUTE = 'add-manual-advisory';
    const EDIT_DEFECT_ROUTE = 'edit-defect';
    const REMOVE_DEFECT_ROUTE = 'remove-defect';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * DefectJourneyContextProvider constructor.
     *
     * @param Router  $router
     * @param Request $request
     */
    public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        $match = $this->router->match($this->request);

        if (!$match instanceof RouteMatch) {
            return self::NO_CONTEXT;
        }

        $routeName = $match->getMatchedRouteName();
        $strlen = strlen($routeName);

        if (strrpos($routeName, self::DEFECTS_TOP_LEVEL_ROUTE.'/search', -$strlen) !== false) {
            return self::SEARCH_CONTEXT;
        } elseif (strrpos($routeName, self::DEFECTS_TOP_LEVEL_ROUTE.'/categories/category', -$strlen) !== false) {
            return self::BROWSE_CATEGORIES_CONTEXT;
        } elseif (strrpos($routeName, self::DEFECTS_TOP_LEVEL_ROUTE.'/categories', -$strlen) !== false) {
            return self::BROWSE_CATEGORIES_ROOT_CONTEXT;
        } elseif (strrpos($routeName, self::MOT_TEST_RESULTS_PARENT_ROUTE, -$strlen) !== false) {
            return self::MOT_TEST_RESULTS_ENTRY_CONTEXT;
        }

        return self::NO_CONTEXT;
    }

    /**
     * @return array
     */
    public static function getAvailableContexts()
    {
        return [
            self::SEARCH_CONTEXT,
            self::BROWSE_CATEGORIES_CONTEXT,
            self::BROWSE_CATEGORIES_ROOT_CONTEXT,
            self::MOT_TEST_RESULTS_ENTRY_CONTEXT,
            self::NO_CONTEXT,
        ];
    }

    /**
     * Depending on the context, get the end of the text that links to the
     * previous screen. E.g., 'Cancel and return to search results', or
     * 'Cancel and return to MOT test results'.
     *
     * @return string
     */
    public function getContextForBackUrlText()
    {
        switch (self::getContext()) {
            case self::SEARCH_CONTEXT: {
                return 'search results';
                break;
            }
            case self::BROWSE_CATEGORIES_CONTEXT: {
                return 'defects';
                break;
            }
            case self::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                return 'defect categories';
                break;
            }
            case self::MOT_TEST_RESULTS_ENTRY_CONTEXT: {
                return 'MOT test results';
                break;
            }
            default: {
                return self::NO_CONTEXT;
            }
        }
    }
}
