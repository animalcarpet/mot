<?php
namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Account\Service\ExpiredPasswordService;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Authn\AuthenticationResultCode;
use DvsaCommon\Dto\Authn\AuthenticationResponseDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaFeature\FeatureToggles;
use Zend\Authentication\AuthenticationService;
use DvsaCommon\HttpRestJson\Client;
use Zend\Session\SessionManager;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\LoginLandingPage;

class WebLoginService
{
    /** @var  AuthenticationService */
    private $authenticationService;

    /** @var  Client */
    private $client;

    /** @var  DtoReflectiveDeserializer */
    private $deserializer;

    /** @var  SessionManager */
    private $sessionManager;

    /** @var  WebAuthenticationCookieService */
    private $authenticationCookieService;

    /** @var  ExpiredPasswordService */
    private $expiredPasswordService;

    /** @var  LazyMotFrontendAuthorisationService */
    private $authorisationService;

    /** @var  TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    /** @var AuthorisationService */
    private $authorisationServiceClient;

    /**
     * @param AuthenticationService $authenticationService
     * @param Client $client
     * @param DtoReflectiveDeserializer $deserializer
     * @param SessionManager $sessionManager
     * @param WebAuthenticationCookieService $cookieService
     * @param ExpiredPasswordService $expiredPasswordService
     * @param LazyMotFrontendAuthorisationService $authorisationService
     * @param TwoFaFeatureToggle $twoFaFeatureToggle
     * @param AuthorisationService $authorisationServiceClient
     */
    public function __construct(
        AuthenticationService $authenticationService,
        Client $client,
        DtoReflectiveDeserializer $deserializer,
        SessionManager $sessionManager,
        WebAuthenticationCookieService $cookieService,
        ExpiredPasswordService $expiredPasswordService,
        LazyMotFrontendAuthorisationService $authorisationService,
        TwoFaFeatureToggle $twoFaFeatureToggle,
        AuthorisationService $authorisationServiceClient
    ) {
        $this->authenticationService = $authenticationService;
        $this->client = $client;
        $this->deserializer = $deserializer;
        $this->sessionManager = $sessionManager;
        $this->authenticationCookieService = $cookieService;
        $this->expiredPasswordService = $expiredPasswordService;
        $this->authorisationService = $authorisationService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
        $this->authorisationServiceClient = $authorisationServiceClient;
    }

    /**
     * @param $username
     * @param $password
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
        $this->sessionManager->regenerateId(true);
        $this->authenticationService->getStorage()->write($this->mapResponseToIdentity($responseDto));
        $this->authenticationCookieService->setUpCookie($token);
        $this->client->setAccessToken($token);

        $this->expiredPasswordService->sentExpiredPasswordNotificationIfNeeded($token,
            $responseDto->getUser()->getPasswordExpiryDate());

        if ($this->userMaySeeA2FAPageAfterLogin()) {
            if ($this->authenticationService->getIdentity()->isSecondFactorRequired()) {
                return $loginResult->setTwoFaPage(LoginLandingPage::LOG_IN_WITH_2FA);
            }
            if ($this->authorisationService->isTradeUser()) {
                return $loginResult->setTwoFaPage(LoginLandingPage::ACTIVATE_2FA_EXISTING_USER);
            }
            if ($this->userHasAlreadyOrderedACard($token)) {
                return $loginResult->setTwoFaPage(LoginLandingPage::ACTIVATE_2FA_NEW_USER);
            }
            return $loginResult->setTwoFaPage(LoginLandingPage::ORDER_2FA_NEW_USER);
        }
        return $loginResult;
    }

    private function authenticateWithApi($username, $password) {
        $restResult = $this->client->post((new UrlBuilder())->session()->toString(),
            ['username' => $username, 'password' => $password]
        );
        /** @var AuthenticationResponseDto $responseDto */
        return $this->deserializer->deserialize($restResult['data'], AuthenticationResponseDto::class);
    }

    private function mapResponseToIdentity(AuthenticationResponseDto $responseDto) {
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

    /**
     * Determine if the user has ever ordered a card
     *
     * @return boolean
     */
    private function userHasAlreadyOrderedACard($token)
    {
        $identity = $this->authenticationService->getIdentity();

        /** @var Collection $orders */
        $orders = $this->authorisationServiceClient->getSecurityCardOrders($identity->getUsername(), null, null,  $token);
        return $orders->getCount() > 0;
    }

    /**
     * Determine if the user should see an activation
     * or order card screen after login
     * (2FA toggle is on) AND (NOT DVSA User) AND has2FAPermission AND (NOT alreadyRegisteredFor2Fa)
     * @return boolean
     */
    private function userMaySeeA2FAPageAfterLogin()
    {
        return $this->twoFaFeatureToggle->isEnabled()
            && !$this->authorisationService->isDvsa()
            && ($this->authorisationService->isGranted(PermissionInSystem::AUTHENTICATE_WITH_2FA)
            || $this->authorisationService->isNewTester());
    }
}