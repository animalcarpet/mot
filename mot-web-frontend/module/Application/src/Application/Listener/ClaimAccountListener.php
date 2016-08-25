<?php

namespace Application\Listener;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Mvc\MvcEvent;

/**
 * User who has not claimed account should has access only to claim account page.
 * User who has claimed account should not has access to claim account page.
 *
 * Class ClaimAccountListener
 * @package Application\Listener
 */
class ClaimAccountListener
{

    const CLAIM_ACCOUNT_SUCCESS_ROUTE = 'account/claim/success';

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    private $whiteList = [
        'login',
        'logout',
        'forgotten-password/update-password',
    ];

    private $claimAccountRoutes = [
        'account/claim',
        'account/claim/confirmEmailAndPassword',
        'account/claim/setSecurityQuestion',
        'account/claim/success',
        'account/claim/review',
        'account/claim/reset',
    ];

    public function __construct(MotIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    public function __invoke(MvcEvent $event)
    {
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $identity = $this->getIdentity();

        $redirectUrl = null;
        if (!in_array($routeName, $this->whiteList)
            && !in_array($routeName, $this->claimAccountRoutes)
            && $identity
            && $identity->isAccountClaimRequired()
        ) {
            $redirectUrl = $event->getRouter()->assemble([], ['name' => 'account/claim']);
        } elseif (in_array($routeName, $this->claimAccountRoutes)
            && $identity
            && !$identity->isAccountClaimRequired()
            && $routeName != self::CLAIM_ACCOUNT_SUCCESS_ROUTE
        ) {
            $redirectUrl = $event->getRouter()->assemble([], ['name' => 'user-home']);
        }

        if ($redirectUrl) {
            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $redirectUrl);
            $response->setStatusCode(302);
            $response->sendHeaders();
            $event->stopPropagation();
        }
    }

    /**
     * @return Identity
     */
    private function getIdentity()
    {
        return $this->identityProvider->getIdentity();
    }
}
