<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Identity;
use DateTimeZone;

class RegisterCardInformationCookieService
{
    private $name;

    const COOKIE_NAME = '_hasSeenCardInformation';

    /**
     * @var MotFrontendIdentityProvider
     */
    private $identityProvider;

    public function __construct(MotFrontendIdentityProvider $identityProvider)
    {
        $this->identityProvider = $identityProvider;
        $this->name = self::COOKIE_NAME;
    }

    /**
     * @param Response $response
     */
    public function addRegisterCardInformationCookie(Response $response)
    {
        $secure = true;
        $path = '/' . RegisterCardInformationController::REGISTER_CARD_INFORMATION_ROUTE . '/' .
            $this->identityProvider->getIdentity()->getUserId();

        $expires   = new \DateTime("tomorrow", new DateTimeZone('Europe/London'));
        $value = 1;

        $cookie = new SetCookie(
            $this->name,
            $value,
            $expires,
            $path,
            null,
            $secure,
            true
        );

        $response->getHeaders()->addHeader($cookie);
    }

    /**
     * Verifies if user has already seen the card information page
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request)
    {
        /** @var array $cookies */
        $cookies = $request->getCookie();

        if (isset($cookies[$this->name])) {
            return true;
        }
        return false;
    }

}