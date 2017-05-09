<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use DateTimeZone;

class AlreadyOrderedCardCookieService
{
    const COOKIE_NAME = '_hasSeenOrderedCardLandingPage';

    private $identityProvider;

    public function __construct(MotIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param Response $response
     */
    public function addAlreadyOrderedCardCookie(Response $response)
    {
        $secure = true;
        $path = '/'.LostOrForgottenCardController::START_ROUTE;

        $expires = new \DateTime('tomorrow', new DateTimeZone('Europe/London'));
        $userId = $this->identityProvider->getIdentity()->getUserId();

        $cookie = new SetCookie(
            self::COOKIE_NAME.$userId,
            $userId,
            $expires,
            $path,
            null,
            $secure,
            true
        );

        $response->getHeaders()->addHeader($cookie);
    }

    /**
     * Verifies if user has already seen the Order landing page.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function hasSeenOrderLandingPage(Request $request)
    {
        /** @var array $cookies */
        $cookies = $request->getCookie();
        $userId = $this->identityProvider->getIdentity()->getUserId();

        return isset($cookies[self::COOKIE_NAME.$userId]);
    }
}
