<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Service;

use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardValidation;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Authentication\AuthenticationService;

class RegisteredCardServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AuthorisationService
     */
    private $authorisationServiceClient;

    public function setUp()
    {
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
        $this->authenticationService = XMock::of(AuthenticationService::class);
    }

    public function test_wheniValidatePin_andPinIsValid_iShouldGetTrueAsResponse()
    {
        $data = new \stdClass();
        $data->pinValid = true;

        $this->authorisationServiceClient
            ->expects($this->once())
            ->method('validatePersonSecurityCard')
            ->willReturn(new SecurityCardValidation($data));

        $pin = '123456';
        $identity = new Identity();
        $this->withCurrentIdentity($identity);
        $this->clientsReturnsValidationResponseForPin($pin, true);

        $response = $this->createService()->validatePin($pin);

        $this->assertTrue($response);
    }

    public function test_wheniValidatePin_andPinIsInvalid_iShouldGetFalseAsResponse()
    {
        $data = new \stdClass();
        $data->pinValid = false;

        $this->authorisationServiceClient
            ->expects($this->once())
            ->method('validatePersonSecurityCard')
            ->willReturn(new SecurityCardValidation($data));

        $pin = '123456';
        $this->clientsReturnsValidationResponseForPin($pin, false);

        $response = $this->createService()->validatePin($pin);

        $this->assertTrue($response === false);
    }

    public function test_wheniValidatePin_andPinIsValid_iShouldHaveAuthenticatedWith2FAFlagSetToTrue()
    {
        $data = new \stdClass();
        $data->pinValid = true;

        $this->authorisationServiceClient
            ->expects($this->once())
            ->method('validatePersonSecurityCard')
            ->willReturn(new SecurityCardValidation($data));

        $pin = '123456';
        $identity = new Identity();
        $this->withCurrentIdentity($identity);
        $this->clientsReturnsValidationResponseForPin($pin, true);

        $this->createService()->validatePin($pin);

        $this->assertTrue($identity->isAuthenticatedWith2FA());
    }

    public function test_givenIWantToRetrieveSecurityCardNumber_whenAuthorisationClientsReturns_iShouldReceiveIsAsResponse()
    {
        $this->withCurrentIdentity(new Identity());

        $serialNumber = 'STTA12345678';
        $this->authorisationServiceClient->expects($this->once())->method('getSecurityCardForUser')
            ->willReturn(new SecurityCard((object) ['serialNumber' => $serialNumber]));

        $returnedSerialNumber = $this->createService()->getSerialNumber();

        $this->assertEquals($returnedSerialNumber, $serialNumber);
    }

    public function test_givenIWantToRetrieveSecurityCardNumber_whenAuthorisationClientsThrowsRequestException_iShouldReceiveEmptyString()
    {
        $this->withCurrentIdentity(new Identity());

        $requestException = XMock::of(ResourceNotFoundException::class);
        $this->authorisationServiceClient->expects($this->once())->method('getSecurityCardForUser')
            ->willThrowException($requestException);

        $returnedSerialNumber = $this->createService()->getSerialNumber();

        $this->assertEquals('', $returnedSerialNumber);
    }

    public function test_whenSecondFactorIsRequiredAndUserHasNotAuthenticatedWith2FA_userIsApplicableFor2FALogin()
    {
        $identity = new Identity();
        $identity->setAuthenticatedWith2FA(false);
        $identity->setSecondFactorRequired(true);

        $this->withCurrentIdentity($identity);

        $this->assertTrue($this->createService()->is2FALoginApplicableToCurrentUser());
    }

    public function test_whenSecondFactorIsNotRequired_userIsNotApplicableFor2FALogin()
    {
        $identity = new Identity();
        $identity->setAuthenticatedWith2FA(false);
        $identity->setSecondFactorRequired(false);

        $this->withCurrentIdentity($identity);

        $this->assertFalse($this->createService()->is2FALoginApplicableToCurrentUser());
    }

    public function test_whenAlreadyAuthenticated_userIsNotApplicableFor2FALogin()
    {
        $identity = new Identity();
        $identity->setAuthenticatedWith2FA(true);

        $this->withCurrentIdentity($identity);

        $this->assertFalse($this->createService()->is2FALoginApplicableToCurrentUser());
    }

    private function withCurrentIdentity($identity)
    {
        $this->authenticationService->expects($this->once())->method('getIdentity')->willReturn($identity);
    }

    private function clientsReturnsValidationResponseForPin($pin, $response)
    {
        $this->authorisationServiceClient->expects($this->once())->method('validatePersonSecurityCard')
            ->with($pin)->willReturn($response);
    }

    private function createService()
    {
        return new RegisteredCardService($this->authenticationService, $this->authorisationServiceClient);
    }
}
