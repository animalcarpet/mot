<?php

namespace Application\Listener;

use Account\Service\ExpiredPasswordService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use Zend\Log\LoggerInterface;
use Zend\Mvc\MvcEvent;

/**
 * User who has expired password has access to only reset password functionality
 *
 * Class PasswordExpiredListener
 * @package Application\Listener
 */
class ExpiredPasswordListener
{
    const CLAIM_ACCOUNT_SUCCESS_ROUTE = 'account/claim/success';

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    private $timeHolder;

    private $logger;

    private $expiredPasswordService;

    public function __construct(
        MotIdentityProviderInterface $identityProvider,
        DateTimeHolder $timeHolder,
        LoggerInterface $logger,
        ExpiredPasswordService $expiredPasswordService
    )
    {
        $this->identityProvider = $identityProvider;
        $this->timeHolder = $timeHolder;
        $this->logger = $logger;
        $this->expiredPasswordService = $expiredPasswordService;
    }

    public function __invoke(MvcEvent $event)
    {
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $identity = $this->getIdentity();

        if (!$identity) {
            // not logged in, move on
            return;
        }

        if (!$this->isRouteRestricted($routeName)) {
            // the route is white-listed so we're good
            return;
        }

        if (!$identity->hasPasswordExpired()) {
            // we don't care when password is still valid
            return;
        }

        $expirationDate = $this->expiredPasswordService->calculatePasswordChangePromptDate($identity->getPasswordExpiryDate());

        $now = $this->timeHolder->getCurrent();

        if ($now < $expirationDate) {
            // password has not expired, so no problem

            $identity->setPasswordExpired(false);

            return;
        }

        $personId = $this->identityProvider->getIdentity()->getUserId();

        $redirectUrl = $event->getRouter()->assemble(
            ['id' => $personId], ['name' => ContextProvider::YOUR_PROFILE_PARENT_ROUTE . '/change-password']
        );

        
        if ($redirectUrl) {
            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $redirectUrl);
            $response->setStatusCode(302);
            $response->sendHeaders();
            $event->stopPropagation();
        }
    }

    private function isRouteRestricted($routeName)
    {
        return !in_array($routeName, $this->getWhitelist());
    }

    /**
     * @return Identity
     */
    private function getIdentity()
    {
        return $this->identityProvider->getIdentity();
    }

    private function getWhitelist()
    {
        return [
            'login',
            'logout',
            'login-2fa',
            'forgotten-password/update-password',
            'account/claim',
            'account/claim/confirmPassword',
            'account/claim/setSecurityQuestion',
            'account/claim/displayPin',
            'account/claim/review',
            'account/claim/reset',
            'user-home/profile/change-password',
            'user-home/profile/change-password/confirmation',
            ContextProvider::YOUR_PROFILE_PARENT_ROUTE . '/change-password',
            ContextProvider::YOUR_PROFILE_PARENT_ROUTE . '/change-password/confirmation',
            'survey',
            'cookies',
        ];
    }
}
