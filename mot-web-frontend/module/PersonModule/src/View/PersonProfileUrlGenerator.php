<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\View;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface as Router;

/**
 * PersonProfileUrlGenerator generates a URL of a person profile given the context (AE, VTS, User search, Your profile).
 */
class PersonProfileUrlGenerator
{
    /**
     * @var ContextProvider
     */
    private $contextProvider;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

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
     * @param Router                       $router
     * @param Request                      $request
     * @param ContextProvider              $contextProvider
     * @param MotIdentityProviderInterface $identityProvider
     */
    public function __construct(Router $router, Request $request, ContextProvider $contextProvider,
                                MotIdentityProviderInterface $identityProvider)
    {
        $this->router = $router;
        $this->request = $request;
        $this->contextProvider = $contextProvider;
        $this->identityProvider = $identityProvider;
    }

    /**
     * `PersonProfileGenerator::toPersonProfile()` should be used to redirect to the profile page when we are in a
     * subroute of the profile. The `id` param represents the person ID and is required.
     *
     * @return string
     */
    public function toPersonProfile()
    {
        $context = $this->contextProvider->getContext();
        $personId = (int) $this->getParamFromRoute('id');

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             *
             * NOTE: The person 'id' parameter is optional in 'Your profile' contexts.
             */
            $params = ['id' => $personId ?: $this->getLoggedInPersonId()];

            return $this->generateUrlFromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE, $params);
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            return $this->generateUrlFromRoute(ContextProvider::USER_SEARCH_PARENT_ROUTE, ['id' => $personId]);
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->getParamFromRoute('authorisedExaminerId');

            return $this->generateUrlFromRoute(ContextProvider::AE_PARENT_ROUTE, [
                'authorisedExaminerId' => $aeId, 'id' => $personId, ]);
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $this->getParamFromRoute('vehicleTestingStationId');

            return $profileUrl = $this->generateUrlFromRoute(ContextProvider::VTS_PARENT_ROUTE, [
                'vehicleTestingStationId' => $vtsId, 'id' => $personId, ]);
        }

        return $this->generateUrlFromRoute(ContextProvider::YOUR_PROFILE_PARENT_ROUTE);
    }

    /**
     * `PersonProfileGenerator::fromPersonProfile()` should be used to redirect to an AE, VTS, User search page when we
     * are in a person's profile page.
     *
     * @param string     $subRouteName
     * @param array      $params       Parameters to use in url generation, if any
     * @param array|bool $options      RouteInterface-specific options to use in url generation, if any
     *
     * @return string
     */
    public function fromPersonProfile($subRouteName = '', array $params = [], array $options = [])
    {
        $context = $this->contextProvider->getContext();
        $personId = (int) $this->getParamFromRoute('id');

        if (ContextProvider::YOUR_PROFILE_CONTEXT === $context) {
            /*
             * Your Profile context.
             *
             * NOTE: The person 'id' parameter is optional in 'Your profile' contexts.
             */
            $params = array_merge(['id' => $personId ?: $this->getLoggedInPersonId()], $params);
            $route = ContextProvider::YOUR_PROFILE_PARENT_ROUTE.'/'.$subRouteName;
        } elseif (ContextProvider::USER_SEARCH_CONTEXT === $context) {
            /*
             * User search context.
             */
            $params = array_merge(['id' => $personId], $params);
            $route = ContextProvider::USER_SEARCH_PARENT_ROUTE.'/'.$subRouteName;
        } elseif (ContextProvider::AE_CONTEXT === $context) {
            /*
             * AE context.
             */
            $aeId = $this->getParamFromRoute('authorisedExaminerId');
            $params = array_merge(['authorisedExaminerId' => $aeId, 'id' => $personId], $params);
            $route = ContextProvider::AE_PARENT_ROUTE.'/'.$subRouteName;
        } elseif (ContextProvider::VTS_CONTEXT === $context) {
            /*
             * VTS context.
             */
            $vtsId = $this->getParamFromRoute('vehicleTestingStationId');
            $params = array_merge(['vehicleTestingStationId' => $vtsId, 'id' => $personId], $params);
            $route = ContextProvider::VTS_PARENT_ROUTE.'/'.$subRouteName;
        } else {
            $route = ContextProvider::YOUR_PROFILE_PARENT_ROUTE;
            $params = [];
        }

        $route = rtrim($route, '/');

        return $this->generateUrlFromRoute($route, $params, $options);
    }

    /**
     * @return string|null
     */
    private function getParamFromRoute($param)
    {
        $match = $this->router->match($this->request);
        if (!$match instanceof RouteMatch) {
            return null;
        }

        $params = $match->getParams();

        return isset($params[$param]) ? (int) $params[$param] : null;
    }

    /**
     * Generates a URL based on a route.
     *
     * @param string     $route   RouteInterface name
     * @param array      $params  Parameters to use in url generation, if any
     * @param array|bool $options RouteInterface-specific options to use in url generation, if any
     *
     * @return string
     */
    private function generateUrlFromRoute($route, $params = [], $options = [])
    {
        $options['name'] = $route;

        return $this->router->assemble($params, $options);
    }

    /**
     * @return int
     */
    private function getLoggedInPersonId()
    {
        return (int) $this->identityProvider->getIdentity()->getUserId();
    }
}
