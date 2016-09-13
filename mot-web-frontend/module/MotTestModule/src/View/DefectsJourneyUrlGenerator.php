<?php

namespace Dvsa\Mot\Frontend\MotTestModule\View;

use Dvsa\Mot\Frontend\MotTestModule\Exception\RouteNotAllowedInContextException;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefect;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface as Router;

class DefectsJourneyUrlGenerator
{
    const MOT_TEST_ID_PARAM = 'motTestNumber';
    const CATEGORY_ID_PARAM = 'categoryId';
    const DEFECT_ID_PARAM = 'defectId';
    const DEFECT_TYPE_PARAM = 'type';
    const IDENTIFIED_DEFECT_ID_PARAM = 'defectItemId';

    /**
     * @var Router
     */
    private $router;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var DefectsJourneyContextProvider
     */
    private $contextProvider;

    /**
     * DefectsJourneyUrlGenerator constructor.
     *
     * @param Router                        $router
     * @param Request                       $request
     * @param DefectsJourneyContextProvider $contextProvider
     */
    public function __construct(Router $router, Request $request, DefectsJourneyContextProvider $contextProvider)
    {
        $this->router = $router;
        $this->request = $request;
        $this->contextProvider = $contextProvider;
    }

    /**
     * Generates url to add-defect route depending on context.
     *
     * @param int    $defectId
     * @param string $defectType Severity of a defect: advisory, prs ,failure
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toAddDefect($defectId, $defectType)
    {
        $context = $this->contextProvider->getContext();
        $params = [
            self::MOT_TEST_ID_PARAM => $this->getParamFromRoute(self::MOT_TEST_ID_PARAM),
            self::CATEGORY_ID_PARAM => $this->getParamFromRoute(self::CATEGORY_ID_PARAM),
            self::DEFECT_ID_PARAM => $defectId,
            self::DEFECT_TYPE_PARAM => $defectType,
        ];
        $options = ['query' => $this->request->getQuery()->toArray()];

        $route = '';
        switch ($context) {
            case DefectsJourneyContextProvider::SEARCH_CONTEXT:
                if (true === $this->isManualAdvisory($defectId, $defectType)) {
                    $route = sprintf('%s/%s/%s',
                        DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                        DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE,
                        DefectsJourneyContextProvider::ADD_MANUAL_ADVISORY_ROUTE
                    );
                } else {
                    $route = sprintf('%s/%s/%s',
                        DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                        DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE,
                        DefectsJourneyContextProvider::ADD_DEFECT_ROUTE
                    );
                }
                break;
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT:
                if (true === $this->isManualAdvisory($defectId, $defectType)) {
                    $route = sprintf('%s/%s/%s/%s',
                        DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                        DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                        DefectsJourneyContextProvider::CATEGORY_ROUTE,
                        DefectsJourneyContextProvider::ADD_MANUAL_ADVISORY_ROUTE
                    );
                } else {
                    $route = sprintf('%s/%s/%s/%s',
                        DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                        DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                        DefectsJourneyContextProvider::CATEGORY_ROUTE,
                        DefectsJourneyContextProvider::ADD_DEFECT_ROUTE
                    );
                }
                break;
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT:
                if (true === $this->isManualAdvisory($defectId, $defectType)) {
                    $route = sprintf('%s/%s/%s',
                        DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                        DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                        DefectsJourneyContextProvider::ADD_MANUAL_ADVISORY_ROUTE
                    );
                    break;
                }
            case DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT:
            case DefectsJourneyContextProvider::NO_CONTEXT:
                throw new RouteNotAllowedInContextException();
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toAddManualAdvisory()
    {
        $context = $this->contextProvider->getContext();
        $params = [
            self::MOT_TEST_ID_PARAM => $this->getParamFromRoute(self::MOT_TEST_ID_PARAM),
            self::CATEGORY_ID_PARAM => $this->getParamFromRoute(self::CATEGORY_ID_PARAM),
            self::DEFECT_ID_PARAM => 0,
        ];
        $options = [
            'query' => $this->request->getQuery()->toArray(),
        ];

        $route = '';
        switch ($context) {
            case DefectsJourneyContextProvider::SEARCH_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE,
                    DefectsJourneyContextProvider::ADD_MANUAL_ADVISORY_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::ADD_MANUAL_ADVISORY_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT: {
                $route = sprintf('%s/%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::CATEGORY_ROUTE,
                    DefectsJourneyContextProvider::ADD_MANUAL_ADVISORY_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT:
            case DefectsJourneyContextProvider::NO_CONTEXT: {
                throw new RouteNotAllowedInContextException();
            }
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * @param int $identifiedDefectId - id of defect that was already added to specified motTest
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toEditDefect($identifiedDefectId)
    {
        $context = $this->contextProvider->getContext();
        $params = [
            self::MOT_TEST_ID_PARAM => $this->getParamFromRoute(self::MOT_TEST_ID_PARAM),
            self::CATEGORY_ID_PARAM => $this->getParamFromRoute(self::CATEGORY_ID_PARAM),
            self::IDENTIFIED_DEFECT_ID_PARAM => $identifiedDefectId,
        ];
        $options = [
            'query' => $this->request->getQuery()->toArray(),
        ];

        $route = '';
        switch ($context) {
            case DefectsJourneyContextProvider::SEARCH_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE,
                    DefectsJourneyContextProvider::EDIT_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::EDIT_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT: {
                $route = sprintf('%s/%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::CATEGORY_ROUTE,
                    DefectsJourneyContextProvider::EDIT_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT: {
                $route = sprintf('%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::EDIT_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::NO_CONTEXT: {
                throw new RouteNotAllowedInContextException();
            }
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * @param int $identifiedDefectId Id of a defect attached to mot test
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toRemoveDefect($identifiedDefectId)
    {
        $context = $this->contextProvider->getContext();
        $params = [
            self::MOT_TEST_ID_PARAM => $this->getParamFromRoute(self::MOT_TEST_ID_PARAM),
            self::CATEGORY_ID_PARAM => $this->getParamFromRoute(self::CATEGORY_ID_PARAM),
            self::IDENTIFIED_DEFECT_ID_PARAM => $identifiedDefectId,
        ];
        $options = [
            'query' => $this->request->getQuery()->toArray(),
        ];

        switch ($context) {
            case DefectsJourneyContextProvider::SEARCH_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE,
                    DefectsJourneyContextProvider::REMOVE_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::REMOVE_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT: {
                $route = sprintf('%s/%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::CATEGORY_ROUTE,
                    DefectsJourneyContextProvider::REMOVE_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT: {
                $route = sprintf('%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::REMOVE_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::NO_CONTEXT: {
                throw new RouteNotAllowedInContextException();
            }
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * @param int $identifiedDefectId Id of a defect attached to mot test
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toRepairDefect($identifiedDefectId)
    {
        $context = $this->contextProvider->getContext();
        $route = '';
        $params = [
            self::MOT_TEST_ID_PARAM => $this->getParamFromRoute(self::MOT_TEST_ID_PARAM),
            self::CATEGORY_ID_PARAM => $this->getParamFromRoute(self::CATEGORY_ID_PARAM),
            self::IDENTIFIED_DEFECT_ID_PARAM => $identifiedDefectId,
        ];
        $options = [
            'query' => $this->request->getQuery()->toArray(),
        ];

        switch ($context) {
            case DefectsJourneyContextProvider::SEARCH_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE,
                    DefectsJourneyContextProvider::REPAIR_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::REPAIR_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT: {
                $route = sprintf('%s/%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::CATEGORY_ROUTE,
                    DefectsJourneyContextProvider::REPAIR_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT: {
                $route = sprintf('%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::REPAIR_DEFECT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::NO_CONTEXT: {
                throw new RouteNotAllowedInContextException();
            }
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * get "back" url from add/add manual advisory/edit/remove defect actions.
     */
    public function goBack()
    {
        $context = $this->contextProvider->getContext();
        $params = [
            self::MOT_TEST_ID_PARAM => $this->getParamFromRoute(self::MOT_TEST_ID_PARAM),
            self::CATEGORY_ID_PARAM => $this->getParamFromRoute(self::CATEGORY_ID_PARAM),
        ];
        $options = [
            'query' => $this->request->getQuery()->toArray(),
        ];

        $route = '';
        switch ($context) {
            case DefectsJourneyContextProvider::SEARCH_CONTEXT: {
                $route = sprintf('%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::SEARCH_PARENT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_CONTEXT: {
                $route = sprintf('%s/%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE,
                    DefectsJourneyContextProvider::CATEGORY_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::BROWSE_CATEGORIES_ROOT_CONTEXT: {
                $route = sprintf('%s/%s',
                    DefectsJourneyContextProvider::DEFECTS_TOP_LEVEL_ROUTE,
                    DefectsJourneyContextProvider::BROWSE_CATEGORIES_PARENT_ROUTE
                );
                break;
            }
            case DefectsJourneyContextProvider::MOT_TEST_RESULTS_ENTRY_CONTEXT: {
                $route = DefectsJourneyContextProvider::MOT_TEST_RESULTS_PARENT_ROUTE;
                break;
            }
            case DefectsJourneyContextProvider::NO_CONTEXT: {
                throw new RouteNotAllowedInContextException();
            }
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * Generates an URL base on route name.
     *
     * @param string $routeName
     * @param array  $params
     * @param array  $options
     *
     * @return string
     */
    private function generateUrlFromRoute($routeName, $params = [], $options = [])
    {
        $options['name'] = $routeName;

        return $this->router->assemble($params, $options);
    }

    /**
     * @param string $param
     *
     * @return mixed|null
     */
    private function getParamFromRoute($param)
    {
        $match = $this->router->match($this->request);
        if (!$match instanceof RouteMatch) {
            return;
        }

        $params = $match->getParams();

        return isset($params[$param]) ? $params[$param] : null;
    }

    /**
     * @param int    $defectId
     * @param string $defectType Severity of a defect: advisory, prs ,failure
     *
     * @return bool
     */
    private function isManualAdvisory($defectId, $defectType)
    {
        return !$defectId && IdentifiedDefect::ADVISORY === $defectType;
    }
}
