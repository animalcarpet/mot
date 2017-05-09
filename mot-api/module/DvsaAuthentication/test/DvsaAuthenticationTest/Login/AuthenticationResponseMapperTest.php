<?php

namespace DvsaAuthenticationTest\Login;

use DvsaAuthentication\Identity;
use DvsaAuthentication\Login\AuthenticationResponseMapper;
use DvsaAuthentication\Login\Response\AccountLockedAuthenticationFailure;
use DvsaAuthentication\Login\Response\AuthenticationSuccess;
use DvsaAuthentication\Login\Response\GenericAuthenticationFailure;
use DvsaAuthentication\Login\Response\InvalidCredentialsAuthenticationFailure;
use DvsaAuthentication\Login\Response\LockoutWarningAuthenticationFailure;
use DvsaAuthentication\Login\Response\UnresolvableIdentityAuthenticationFailure;
use DvsaCommon\Authn\AuthenticationResultCode;
use DvsaCommon\Dto\Common\KeyValue;
use DvsaCommon\Enum\PersonAuthType;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use Zend\Authentication\Result;

class AuthenticationResponseMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMapToDto_givenSuccessfulResponse_shouldExpectCorrectDtoState()
    {
        $identity = new Identity($this->defaultPerson());
        $identity->setToken('customToken')->setPasswordExpiryDate($this->aDate());
        $successfulResponse = new AuthenticationSuccess($identity);

        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($successfulResponse, 'customUsername1');

        $this->assertEquals($successfulResponse->getIdentity()->getToken(), $result->getAccessToken());
        $this->assertEquals(AuthenticationResultCode::SUCCESS, $result->getAuthnCode());
        $this->assertCount(1, $result->getMessages());
        $this->assertEquals($identity->getUsername(), $result->getUser()->getUsername());
        $this->assertEquals($identity->getUserId(), $result->getUser()->getUserId());
        $this->assertEquals($identity->getDisplayName(), $result->getUser()->getDisplayName());
        $this->assertEquals('', $result->getUser()->getRole());
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertTrue($result->isIsValid());
        $this->assertEquals('customUsername1', $result->getIdentity());
    }

    public static function dataProvider_authenticationMethod()
    {
        return [[PersonAuthType::PIN, false], [PersonAuthType::CARD, true]];
    }

    /**
     * @dataProvider dataProvider_authenticationMethod
     */
    public function testMapToDto_givenAuthenticationMethod_shouldSet2FactorFlagAccordingly($authMethod, $flagState)
    {
        $person = $this->defaultPerson();
        $person->setAuthenticationMethod($this->authenticationType($authMethod));
        $identity = (new Identity($person))->setPasswordExpiryDate($this->aDate());
        $successfulResponse = new AuthenticationSuccess($identity);
        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($successfulResponse, 'anything');

        $this->assertEquals($flagState, $result->getUser()->isIsSecondFactorRequired());
    }

    public static function dataProvider_passwordChangeRequired()
    {
        return [[true, true], [false, false]];
    }

    /**
     * @dataProvider dataProvider_passwordChangeRequired
     */
    public function testMapToDto_givenPasswordChangeRequired_shouldSetPasswordChangeRequiredFlagAccordingly(
        $passwordChangeRequired, $passwordChangeRequiredFlag
    ) {
        $person = $this->defaultPerson()->setPasswordChangeRequired($passwordChangeRequired);
        $identity = (new Identity($person))->setPasswordExpiryDate($this->aDate());
        $successfulResponse = new AuthenticationSuccess($identity);
        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($successfulResponse, 'anything');

        $this->assertEquals($passwordChangeRequiredFlag, $result->getUser()->isIsPasswordChangeRequired());
    }

    public static function dataProvider_accountClaimRequired()
    {
        return [[true, true], [false, false]];
    }

    /**
     * @dataProvider dataProvider_accountClaimRequired
     */
    public function testMapToDto_givenAccountClaimRequired_shouldSetAccountClaimFlagAccordingly(
        $accountClaimRequired, $accountClaimRequiredFlag
    ) {
        $person = $this->defaultPerson()->setAccountClaimRequired($accountClaimRequired);
        $identity = (new Identity($person))->setPasswordExpiryDate($this->aDate());
        $successfulResponse = new AuthenticationSuccess($identity);
        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($successfulResponse, 'anything');

        $this->assertEquals($accountClaimRequiredFlag, $result->getUser()->isIsAccountClaimRequired());
    }

    public function testMapToDto_givenAccountLockedResponse_shouldExpectCorrectDtoState()
    {
        $response = new AccountLockedAuthenticationFailure();

        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($response, 'customUsername1');

        $this->assertEquals(AuthenticationResultCode::ACCOUNT_LOCKED, $result->getAuthnCode());
        $this->assertCount(1, $result->getMessages());
        $this->assertNull($result->getUser());
        $this->assertEquals(Result::FAILURE, $result->getCode());
        $this->assertFalse($result->isIsValid());
        $this->assertEquals('customUsername1', $result->getIdentity());
    }

    public function testMapToDto_givenInvalidCredentialsResponse_shouldExpectCorrectDtoState()
    {
        $response = new InvalidCredentialsAuthenticationFailure();

        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($response, 'customUsername1');

        $this->assertEquals(AuthenticationResultCode::INVALID_CREDENTIALS, $result->getAuthnCode());
        $this->assertCount(1, $result->getMessages());
        $this->assertNull($result->getUser());
        $this->assertEquals(Result::FAILURE, $result->getCode());
        $this->assertFalse($result->isIsValid());
        $this->assertEquals('customUsername1', $result->getIdentity());
    }

    public function testMapToDto_givenUnresolvableIdentityResponse_shouldExpectCorrectDtoState()
    {
        $response = new UnresolvableIdentityAuthenticationFailure();

        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($response, 'customUsername1');

        $this->assertEquals(AuthenticationResultCode::UNRESOLVABLE_IDENTITY, $result->getAuthnCode());
        $this->assertCount(1, $result->getMessages());
        $this->assertNull($result->getUser());
        $this->assertEquals(Result::FAILURE, $result->getCode());
        $this->assertFalse($result->isIsValid());
        $this->assertEquals('customUsername1', $result->getIdentity());
    }

    public function testMapToDto_givenLockoutWarningResponse_shouldExpectCorrectDtoState()
    {
        $response = new LockoutWarningAuthenticationFailure(5);

        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($response, 'customUsername1');

        $this->assertEquals(AuthenticationResultCode::LOCKOUT_WARNING, $result->getAuthnCode());
        $this->assertCount(1, $result->getMessages());
        $this->assertNull($result->getUser());
        $this->assertEquals(
            KeyValue::fromMap([LockoutWarningAuthenticationFailure::KEY_ATTEMPTS_LEFT => 5]), $result->getExtra()
        );
        $this->assertEquals(Result::FAILURE, $result->getCode());
        $this->assertFalse($result->isIsValid());
        $this->assertEquals('customUsername1', $result->getIdentity());
    }

    public function testMapToDto_givenGenericAuthenticationFailure_shouldExpectCorrectDtoState()
    {
        $response = new GenericAuthenticationFailure('anything');

        $mapper = new AuthenticationResponseMapper();

        $result = $mapper->mapToDto($response, 'customUsername1');

        $this->assertEquals(AuthenticationResultCode::ERROR, $result->getAuthnCode());
        $this->assertCount(1, $result->getMessages());
        $this->assertNull($result->getUser());
        $this->assertEquals(Result::FAILURE, $result->getCode());
        $this->assertFalse($result->isIsValid());
        $this->assertEquals('customUsername1', $result->getIdentity());
    }

    /**
     * @param $code
     *
     * @return AuthenticationMethod
     */
    private function authenticationType($code)
    {
        return (new AuthenticationMethod())->setCode($code);
    }

    private function defaultPerson()
    {
        return (new Person())
            ->setId(43434)
            ->setUsername('customUsername')
            ->setFirstName('customFirstName')
            ->setAccountClaimRequired(false)
            ->setPasswordChangeRequired(false);
    }

    private function aDate()
    {
        return new \DateTime('20001212121212');
    }
}
