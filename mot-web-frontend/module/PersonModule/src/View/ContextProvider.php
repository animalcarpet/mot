<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\View;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack as Router;
use Zend\Mvc\Router\RouteMatch;

/**
 * ContextProvider uses the current route and request to determine the context (AE, VTS, User search, Your profile).
 */
class ContextProvider implements AutoWireableInterface
{
    // The context in which the user is viewing the profile.
    const AE_CONTEXT = 'ae';
    const NO_CONTEXT = 'none';
    const USER_SEARCH_CONTEXT = 'user-search';
    const VTS_CONTEXT = 'vts';
    const YOUR_PROFILE_CONTEXT = 'your-profile';
    // Parent routes
    const AE_PARENT_ROUTE = 'newProfileAE';
    const USER_SEARCH_PARENT_ROUTE = 'newProfileUserAdmin';
    const VTS_PARENT_ROUTE = 'newProfileVTS';
    const YOUR_PROFILE_PARENT_ROUTE = 'newProfile';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Router
     */
    private $router;

    /**
     * PersonProfileUrlGenerator constructor.
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
        $routeSegments = explode('/', $routeName);
        $parentRoute = reset($routeSegments);

        // Match the route against those in routes.config.php
        switch ($parentRoute) {
            case self::YOUR_PROFILE_PARENT_ROUTE:
                return self::YOUR_PROFILE_CONTEXT;
            case self::AE_PARENT_ROUTE:
                return self::AE_CONTEXT;
            case self::VTS_PARENT_ROUTE:
                return self::VTS_CONTEXT;
            case self::USER_SEARCH_PARENT_ROUTE:
                return self::USER_SEARCH_CONTEXT;
        }

        return self::NO_CONTEXT;
    }

    /**
     * @return array
     */
    public static function getAvailableContexts()
    {
        return [
            self::AE_CONTEXT,
            self::NO_CONTEXT,
            self::USER_SEARCH_CONTEXT,
            self::VTS_CONTEXT,
            self::YOUR_PROFILE_CONTEXT,
        ];
    }

    public function isYourProfileContext()
    {
        return (self::YOUR_PROFILE_CONTEXT === $this->getContext());
    }

    public function isUserSearchContext()
    {
        return (self::USER_SEARCH_CONTEXT === $this->getContext());
    }
}
