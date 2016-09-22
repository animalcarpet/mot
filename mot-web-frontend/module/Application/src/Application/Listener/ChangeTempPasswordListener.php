<?php

namespace Application\Listener;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Mvc\MvcEvent;

/**
 * User who has logged in with an account flagged as having a temporary password should only have access
 * to the change password page
 *
 * Class ChangeTempPasswordListener
 * @package Application\Listener
 */
class ChangeTempPasswordListener
{
    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    private $whiteList = [
        'login',
        'logout',
        'account/claim',
        'account/claim/confirmPassword',
        'account/claim/setSecurityQuestion',
        'account/claim/generatePin',
        'account/claim/reset',
        'account/claim/review',
        'account/claim/success',
        'login-2fa',
        'survey',
    ];

    private $ChangeTempPasswordRoutes = [
        'forgotten-password/update-password'
    ];

    public function __construct(MotIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    public function __invoke(MvcEvent $event)
    {
        $identity = $this->getIdentity();
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $hasTempPassword = ($identity && $identity->isPasswordChangeRequired());

        $redirectUrl = null;
        if (!in_array($routeName, $this->whiteList)
            && $hasTempPassword
            && !in_array($routeName, $this->ChangeTempPasswordRoutes)
        ) {
            $redirectUrl = $event->getRouter()->assemble([], ['name' => 'forgotten-password/update-password']);
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
