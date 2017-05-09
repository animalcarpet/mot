<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Account\Service\ExpiredPasswordService;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use DvsaCommon\Authn\AuthenticationResultCode;
use DvsaCommon\Dto\Authn\AuthenticationResponseDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Zend\Authentication\AuthenticationService;
use DvsaCommon\HttpRestJson\Client;
use Zend\Session\SessionManager;

class WebLoginService
{
    /** @var AuthenticationService */
    private $authenticationService;

    /** @var Client */
    private $client;

    /** @var DtoReflectiveDeserializer */
    private $deserializer;

    /** @var SessionManager */
    private $sessionManager;

    /** @var WebAuthenticationCookieService */
    private $authenticationCookieService;

    /** @var ExpiredPasswordService */
    private $expiredPasswordService;

    /** @var LazyMotFrontendAuthorisationService */
    private $authorisationService;

    /**
     * @param AuthenticationService               $authenticationService
     * @param Client                              $client
     * @param DtoReflectiveDeserializer           $deserializer
     * @param SessionManager                      $sessionManager
     * @param WebAuthenticationCookieService      $cookieService
     * @param ExpiredPasswordService              $expiredPasswordService
     * @param LazyMotFrontendAuthorisationService $authorisationService
     */
    public function __construct(
        AuthenticationService $authenticationService,
        Client $client,
        DtoReflectiveDeserializer $deserializer,
        SessionManager $sessionManager,
        WebAuthenticationCookieService $cookieService,
        ExpiredPasswordService $expiredPasswordService,
        LazyMotFrontendAuthorisationService $authorisationService
    ) {
        $this->authenticationService = $authenticationService;
        $this->client = $client;
        $this->deserializer = $deserializer;
        $this->sessionManager = $sessionManager;
        $this->authenticationCookieService = $cookieService;
        $this->expiredPasswordService = $expiredPasswordService;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return WebLoginResult
     */
    public function login($username, $password)
    {
        $responseDto = $this->authenticateWithApi($username, $password);
        $loginResult = new WebLoginResult();
        $loginResult->setCode($responseDto->getAuthnCode());
        if ($responseDto->getAuthnCode() !== AuthenticationResultCode::SUCCESS) {
            return $loginResult;
        }

        $token = $responseDto->getAccessToken();
        $loginResult->setToken($token);
        $this->sessionManager->regenerateId(true);
        $this->authenticationService->getStorage()->write($this->mapResponseToIdentity($responseDto));
        $this->authenticationCookieService->setUpCookie($token);
        $this->client->setAccessToken($token);

        $this->expiredPasswordService->sentExpiredPasswordNotificationIfNeeded($token,
            $responseDto->getUser()->getPasswordExpiryDate());

        return $loginResult;
    }

    private function authenticateWithApi($username, $password)
    {
        $restResult = $this->client->post((new UrlBuilder())->session()->toString(),
            ['username' => $username, 'password' => $password]
        );
        /* @var AuthenticationResponseDto $responseDto */
        return $this->deserializer->deserialize($restResult['data'], AuthenticationResponseDto::class);
    }

    private function mapResponseToIdentity(AuthenticationResponseDto $responseDto)
    {
        return (new Identity())
            ->setUserId($responseDto->getUser()->getUserId())
            ->setUsername($responseDto->getUser()->getUsername())
            ->setDisplayName($responseDto->getUser()->getDisplayName())
            ->setDisplayRole($responseDto->getUser()->getRole())
            ->setAccessToken($responseDto->getAccessToken())
            ->setPasswordExpiryDate($responseDto->getUser()->getPasswordExpiryDate())
            ->setAccountClaimRequired($responseDto->getUser()->isIsAccountClaimRequired())
            ->setPasswordChangeRequired($responseDto->getUser()->isIsPasswordChangeRequired())
            ->setSecondFactorRequired($responseDto->getUser()->isIsSecondFactorRequired());
    }
}
