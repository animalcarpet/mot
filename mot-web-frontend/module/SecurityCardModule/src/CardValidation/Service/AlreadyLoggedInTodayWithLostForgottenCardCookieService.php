<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Identity;
use DateTimeZone;

class AlreadyLoggedInTodayWithLostForgottenCardCookieService
{
    const COOKIE_NAME = '_hasLoggedInWithLostForgottenCardJourney';

    /** @var MotIdentityProviderInterface $identityProvider*/
    private $identityProvider;

    public function __construct(MotIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param Response $response
     */
    public function addLoggedInViaLostForgottenCardCookie(Response $response)
    {
        $userId = $this->identityProvider->getIdentity()->getUserId();
        $cookie = new SetCookie(
            self::COOKIE_NAME . $userId,
            $userId,
            new \DateTime("tomorrow", new DateTimeZone('Europe/London')),
            '/',
            null,
            true,
            true
        );
        $response->getHeaders()->addHeader($cookie);
    }

    /**
     * Verifies if user has already seen the card information page
     * @param Request $request
     * @return bool
     */
    public function hasLoggedInTodayWithLostForgottenCardJourney(Request $request)
    {
        /** @var array $cookies */
        $cookies = $request->getCookie();
        $userId = $this->identityProvider->getIdentity()->getUserId();
        return isset($cookies[self::COOKIE_NAME . $userId]);
    }

}