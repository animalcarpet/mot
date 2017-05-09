<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Listener;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Mvc\MvcEvent;

class RegisterCardHardStopListener
{
    /**
     * @var array
     */
    private $whiteList = [
        '@register-card@',
        '@register-card/hard-stop@',
        '@login-2fa@',
        '@login@',
        '@logout@',
        '@account-register/create-an-account@',
        '@account-register/create-an-account/(.*)@',
        '@forgotten-password@',
        '@forgotten-password/(.*)@',
        '@lost-or-forgotten-card@',
        '@account/claim@',
        '@account/claim/(.*)@',
    ];

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /** @var RegisterCardHardStopCondition */
    private $condition;

    /**
     * @param MotIdentityProviderInterface  $motIdentityProviderInterface
     * @param RegisterCardHardStopCondition $hardStopCondition
     */
    public function __construct(
        MotIdentityProviderInterface $motIdentityProviderInterface,
        RegisterCardHardStopCondition $hardStopCondition
    ) {
        $this->identityProvider = $motIdentityProviderInterface;
        $this->condition = $hardStopCondition;
    }

    public function __invoke(MvcEvent $event)
    {
        $identity = $this->identityProvider->getIdentity();
        if (!$identity) {
            return false;
        }

        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $isRouteRestricted = $this->isRouteRestricted($routeName);

        if ($isRouteRestricted && $this->condition->isTrue()) {
            $redirectUrl = $event->getRouter()->assemble([], ['name' => 'register-card/hard-stop']);

            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $redirectUrl);
            $response->setStatusCode(302);
            $response->sendHeaders();
            $event->stopPropagation();
        }

        return true;
    }

    /**
     * @param string $routeName
     *
     * @return bool
     */
    private function isRouteRestricted($routeName)
    {
        foreach ($this->whiteList as $preg) {
            if (preg_match($preg, $routeName) > 0) {
                return false;
            }
        }

        return true;
    }
}
