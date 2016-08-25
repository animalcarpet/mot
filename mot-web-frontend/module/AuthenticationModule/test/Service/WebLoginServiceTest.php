<?php

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;

use Account\Service\ExpiredPasswordService;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\LoginLandingPage;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Authn\AuthenticationResultCode;
use DvsaCommon\Dto\Authn\AuthenticatedUserDto;
use DvsaCommon\Dto\Authn\AuthenticationResponseDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;
use Zend\Session\SessionManager;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;

class WebLoginServiceTest extends \PHPUnit_Framework_TestCase
{
    private $authenticationService;
    private $client;
    private $deserializer;
    private $sessionManager;
    private $webAuthenticationCookieService;
    private $expiredPasswordService;
    private $authorisationService;
    private $featureToggles;

    /**
     * @var AuthorisationService
     */
    private $authorisationServiceClient;

    public function setUp()
    {
        $this->authenticationService = (new AuthenticationService())->setStorage(new NonPersistent());
        $this->client = XMock::of(Client::class);
        $this->deserializer = XMock::of(DtoReflectiveDeserializer::class);
        $this->sessionManager = XMock::of(SessionManager::class);
        $this->webAuthenticationCookieService = XMock::of(WebAuthenticationCookieService::class);
        $this->expiredPasswordService = XMock::of(ExpiredPasswordService::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->featureToggles = XMock::of(TwoFaFeatureToggle::class);
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
    }

    public function testLogin_authenticationSuccessful_shouldReturnSuccesful()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();

        $user = $authenticationDto->getUser();

        $this->withAuthenticationDtoFromApi($authenticationDto);

        $loginResult = $this->create()->login('username', 'password');

        $this->assertEquals(AuthenticationResultCode::SUCCESS, $loginResult->getCode());
        /** @var Identity $identity */
        $identity = $this->authenticationService->getStorage()->read();

        $this->assertEquals($user->getUsername(), $identity->getUsername());
        $this->assertEquals($user->getDisplayName(), $identity->getDisplayName());
        $this->assertEquals($user->getUserId(), $identity->getUserId());
        $this->assertEquals($user->isIsSecondFactorRequired(), $identity->isSecondFactorRequired());
        $this->assertEquals($user->isIsAccountClaimRequired(), $identity->isAccountClaimRequired());
        $this->assertEquals($user->isIsPasswordChangeRequired(), $identity->isPasswordChangeRequired());
        $this->assertEquals($authenticationDto->getAccessToken(), $identity->getAccessToken());
    }

    public function testLogin_authenticationSuccessful_shouldRegenerateSession()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->sessionManager->expects($this->once())->method('regenerateId')->with(true);

        $this->create()->login('username', 'password');
    }

    public function testLogin_authenticationSuccessful_shouldSetUpAuthenticationCookie()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->webAuthenticationCookieService->expects($this->once())
            ->method('setUpCookie')->with($authenticationDto->getAccessToken());

        $this->create()->login('username', 'password');
    }

    public function testLogin_authenticationSuccessful_shouldSetAccessTokenOnClient()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->client->expects($this->once())
            ->method('setAccessToken')->with($authenticationDto->getAccessToken());

        $this->create()->login('username', 'password');
    }

    public function testLogin_authenticationSuccessful_shouldCallExpiredPasswordServiceAppropriately()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->expiredPasswordService->expects($this->once())
            ->method('sentExpiredPasswordNotificationIfNeeded')
            ->with($authenticationDto->getAccessToken(), $authenticationDto->getUser()->getPasswordExpiryDate());

        $this->create()->login('username', 'password');
    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOn_and_hasntActivatedACard_and_HasPermissionToAuthenticateWith2Fa_and_isNotNewTester_shouldSetCanActivate2FaToTrue()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission(true);
        $this->with2FaOn();

        $this->authorisationServiceClient->expects($this->once())
            ->method('getSecurityCardOrders')
            ->willReturn(new Collection([], SecurityCardOrder::class));

        $loginResult = $this->create()->login('username', 'password');

        $this->assertEquals(LoginLandingPage::ORDER_2FA_NEW_USER, $loginResult->getTwoFaPage());
    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOn_and_hasntActivatedACard_and_NoPermissionToAuthenticateWith2Fa_and_isNewTester_shouldSetCanActivate2FaToTrue()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission(false);
        $this->withIsNewTester(true);
        $this->with2FaOn();

        $this->authorisationServiceClient->expects($this->once())
            ->method('getSecurityCardOrders')
            ->willReturn(new Collection([], SecurityCardOrder::class));

        $loginResult = $this->create()->login('username', 'password');

        $this->assertEquals(LoginLandingPage::ORDER_2FA_NEW_USER, $loginResult->getTwoFaPage());
    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOn_and_activatedACard_shouldSetCanLoginWith2Fa()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(true);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission(true);
        $this->with2FaOn();

        $loginResult = $this->create()->login('username', 'password');

        $this->assertEquals(LoginLandingPage::LOG_IN_WITH_2FA, $loginResult->getTwoFaPage());

    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOn_and_isNewUser_shouldSeeExistingUserActivationInfoPage()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withIsTradeUser(true);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission(true);
        $this->with2FaOn();

        $loginResult = $this->create()->login('username', 'password');
        $this->assertEquals(LoginLandingPage::ACTIVATE_2FA_EXISTING_USER, $loginResult->getTwoFaPage());
    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOn_and_isNewUser_shouldSeeNewUserActivationInfoPage()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withIsTradeUser(false);

        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission(true);
        $this->with2FaOn();

        $this->authorisationServiceClient->expects($this->once())
            ->method('getSecurityCardOrders')
            ->willReturn(new Collection([new \stdClass($this->orderResponseArray())], SecurityCardOrder::class));

        $loginResult = $this->create()->login('username', 'password');
        $this->assertEquals(LoginLandingPage::ACTIVATE_2FA_NEW_USER, $loginResult->getTwoFaPage());
    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOn_and_hasntPermissionToAuthenticateWith2Fa_shouldNotUseA2FaPage()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission(false);
        $this->with2FaOn();

        $loginResult = $this->create()->login('username', 'password');

        $this->assertNull($loginResult->getTwoFaPage());
    }

    public function testLogin_whenUserIsNotDvsa_and_2FaIsOff_shouldNotUseA2FaPage()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(false);
        $this->withAuthenticateWith2FaPermission();
        $this->with2FaOn(false);

        $loginResult = $this->create()->login('username', 'password');

        $this->assertNull($loginResult->getTwoFaPage());
    }


    public function testLogin_whenUserIsDvsa_shouldNotUseA2FaPage()
    {
        $authenticationDto = $this->withSuccessfulAuthenticationDto();
        $authenticationDto->getUser()->setIsSecondFactorRequired(false);
        $this->withAuthenticationDtoFromApi($authenticationDto);
        $this->withUserBeingDvsa(true);
        $this->withAuthenticateWith2FaPermission();
        $this->with2FaOn();

        $loginResult = $this->create()->login('username', 'password');

        $this->assertNull($loginResult->getTwoFaPage());
    }



    public function testLogin_authenticationFailed()
    {
        $authenticationDto = (new AuthenticationResponseDto())
            ->setAuthnCode(AuthenticationResultCode::INVALID_CREDENTIALS)
            ->setExtra(null)
            ->setUser(null);

        $this->withAuthenticationDtoFromApi($authenticationDto);

        $loginResult = $this->create()->login('username', 'password');

        $this->assertEquals(AuthenticationResultCode::INVALID_CREDENTIALS, $loginResult->getCode());
        /** @var Identity $identity */
        $identity = $this->authenticationService->getStorage()->read();

        $this->assertNull($identity);
    }

    private function withUserBeingDvsa($isDvsa = true)
    {
        $this->authorisationService->expects($this->any())->method('isDvsa')->willReturn($isDvsa);
    }

    private function withAuthenticateWith2FaPermission($hasPermission = true)
    {
        $this->authorisationService->expects($this->any())->method('isGranted')
            ->with(PermissionInSystem::AUTHENTICATE_WITH_2FA)->willReturn($hasPermission);
    }

    private function with2FaOn($twoFaOn = true)
    {
        $this->featureToggles->expects($this->any())->method('isEnabled')->willReturn($twoFaOn);
    }

    private function withAuthenticationDtoFromApi($dto)
    {
        $this->client->expects($this->once())->method('post')
            ->willReturn(['data' => []]);

        $this->deserializer->expects($this->once())->method('deserialize')
            ->willReturn($dto);
    }

    private function withSuccessfulAuthenticationDto()
    {
        return (new AuthenticationResponseDto())
            ->setAuthnCode(AuthenticationResultCode::SUCCESS)
            ->setExtra(null)
            ->setUser((new AuthenticatedUserDto())
                ->setDisplayName('displayName')
                ->setUserId(5)
                ->setUsername('customUsername')
                ->setIsAccountClaimRequired(true)
                ->setIsSecondFactorRequired(true)
                ->setIsPasswordChangeRequired(true));
    }

    private function withIsTradeUser($isTradeUser)
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('isTradeUser')
            ->willReturn($isTradeUser);
    }

    private function withIsNewTester($isNewTester)
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('isNewTester')
            ->willReturn($isNewTester);
    }

    private function orderResponseArray()
    {
        return [
            "submittedOn" => "2014-05-25",
            "fullName" => "AUTH_INT_YOTURWDKSPFUMKBPMGMU AUTH_INT_YOTURWDKSPFUMKBPMGMU",
            "recipientName" => "",
            "addressLine1" => "9f1341",
            "addressLine2" => "5 Uncanny St",
            "addressLine3" => "fake address line 3",
            "postcode" => "L1 1PQ",
            "town" => "Liverpool"
        ];
    }

    private function create()
    {
        return new WebLoginService(
            $this->authenticationService,
            $this->client,
            $this->deserializer,
            $this->sessionManager,
            $this->webAuthenticationCookieService,
            $this->expiredPasswordService,
            $this->authorisationService,
            $this->featureToggles,
            $this->authorisationServiceClient
        );
    }

}