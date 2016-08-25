<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Request\ActivateCardRequest;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use DvsaCommonTest\TestUtils\XMock;
use GuzzleHttp\Exception\RequestException;
use Zend\Authentication\AuthenticationService;

class RegisterCardServiceTest extends \PHPUnit_Framework_TestCase
{

    private $authorisationServiceClient;

    private $identityProvider;

    private $frontendAuthorisationService;

    public function setUp()
    {
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->frontendAuthorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
    }

    public function test_givenIActivateCard_whenIcallAuthorisationServiceProperly_iShouldInteractWithItsClientProperly()
    {
        $identity = (new Identity())->setSecondFactorRequired(false);
        $this->withCurrentIdentity($identity);
        $serialNumber = "STTA12345678";
        $pin = "123456";
        $this->withActivateRequestSuccessful($serialNumber, $pin);

        $this->createService()->registerCard($serialNumber, $pin);

        $this->assertTrue($identity->isSecondFactorRequired());
        $this->assertTrue($identity->isAuthenticatedWith2FA());
    }

    public function test_givenIActivateCard_whenIcallAuthorisationService_andItFails_iShouldNotBeAuthenticatedWith2Fa()
    {
        $identity = (new Identity())->setSecondFactorRequired(false);
        $this->withCurrentIdentity($identity);
        $this->withActivateRequestFailing(ResourceNotFoundException::class);

        try {
            $this->createService()->registerCard('any', 'any');
        } catch(ResourceNotFoundException $e) {}

        $this->assertFalse($identity->isSecondFactorRequired());
        $this->assertFalse($identity->isAuthenticatedWith2FA());
    }

    private function withActivateRequestSuccessful($serialNumber, $pin)
    {
        $activateCardRequest = new ActivateCardRequest();
        $activateCardRequest->setPinNumber($pin)->setSerialNumber($serialNumber);
        $this->authorisationServiceClient->expects($this->once())
            ->method('activatePersonSecurityCard')
            ->with($activateCardRequest);
    }

    private function withActivateRequestFailing($exceptionClass)
    {
        $this->authorisationServiceClient->expects($this->any())
            ->method('activatePersonSecurityCard')
            ->willThrowException(new $exceptionClass);
    }

    public function test_givenSecondFactorIsNotSetRequired_iAmNotRegistered()
    {
        $identity = (new Identity())->setSecondFactorRequired(false);
        $this->withCurrentIdentity($identity);

        $this->assertFalse($this->createService()->isUserRegistered());
    }

    public function test_givenSecondFactorIsSetRequired_iAmRegistered()
    {
        $identity = (new Identity())->setSecondFactorRequired(true);
        $this->withCurrentIdentity($identity);

        $this->assertTrue($this->createService()->isUserRegistered());
    }


    private function withCurrentIdentity($identity)
    {
        $this->identityProvider->expects($this->any())->method('getIdentity')->willReturn($identity);
    }

    private function createService()
    {
        return new RegisterCardService(
            $this->authorisationServiceClient,
            $this->identityProvider
        );
    }
}