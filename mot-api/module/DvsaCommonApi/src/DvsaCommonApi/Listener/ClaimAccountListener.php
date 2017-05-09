<?php

namespace DvsaCommonApi\Listener;

use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Error\ApiErrorCodes;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Model\ApiEndPoint;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

class ClaimAccountListener extends AbstractListenerAggregate
{
    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /** @var ApiEndPoint[] */
    private $whiteList = [];

    public function __construct(MotIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
        $this->whiteList = $this->createWhiteList();
    }

    private function createWhiteList()
    {
        return [
            new ApiEndPoint('session', 'POST'),
            new ApiEndPoint('catalog', 'GET'),
            new ApiEndPoint('identity-data', 'GET'),
            new ApiEndPoint('person/rbac-roles', 'GET'),
            new ApiEndPoint('person/dashboard', 'GET'),
            new ApiEndPoint('AccountApi/default', 'GET'),
            new ApiEndPoint('AccountApi/default', 'PUT'),
            new ApiEndPoint('security-question', 'GET'),
            new ApiEndPoint('person/reset-pin', 'GET'),
            new ApiEndPoint('person/reset-pin', 'PUT'),
            new ApiEndPoint('notification/person', 'GET'),
            new ApiEndPoint('notification/item/read', 'PUT'),
            new ApiEndPoint('notification/item/action', 'PUT'),
        ];
    }

    public function attach(EventManagerInterface $events)
    {
        $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'invoke'], 2);
    }

    public function invoke(MvcEvent $event)
    {
        $identity = $this->getIdentity();

        /** @var Request $request */
        $request = $event->getRequest();
        $webMethod = $request->getMethod();

        $routeName = $event->getRouteMatch()->getMatchedRouteName();

        $endPoint = new ApiEndPoint($routeName, $webMethod);

        if (!$this->isEndPointInWhiteList($endPoint)
            && $identity
            && $identity->isAccountClaimRequired()
        ) {
            $this->throwUnathorisedException($event);
        }
    }

    private function throwUnathorisedException(MvcEvent $event)
    {
        /** @var Request $request */
        $request = $event->getRequest();
        $webMethod = $request->getMethod();

        $routeName = $event->getRouteMatch()->getMatchedRouteName();

        $model = new JsonModel(
            [
                'errors' => [[
                    'message' => 'Forbidden',
                    'code' => ApiErrorCodes::UNAUTHORISED,
                ]],
                'debugInfo' => ['Account claim required. '
                    .'This person has not completed claim account process. '
                    ."Api route: '".$routeName."', Web method: '".$webMethod."'.", ],
            ]
        );

        $response = $event->getResponse();
        $response->setStatusCode(403);

        $event->setViewModel($model);
        $event->setResult($model);
        $event->stopPropagation();
    }

    /**
     * @return Identity
     */
    private function getIdentity()
    {
        return $this->identityProvider->getIdentity();
    }

    private function isEndPointInWhiteList(ApiEndPoint $searchedEndPoint)
    {
        return ArrayUtils::anyMatch(
            $this->whiteList, function (ApiEndPoint $endPoint) use ($searchedEndPoint) {
                return $searchedEndPoint->equals($endPoint);
            }
        );
    }
}
