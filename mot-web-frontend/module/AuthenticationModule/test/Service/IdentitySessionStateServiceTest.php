<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\IdentitySessionState;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use DvsaCommon\Auth\MotIdentityProvider;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Log\Logger;

class IdentitySessionStateServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpenAMClientInterface */
    private $client;

    /** @var MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;
    /** @var WebAuthenticationCookieService $cookieService */
    private $cookieService;

    public function setUp()
    {
        $this->client = XMock::of(OpenAMClientInterface::class);
        $this->motIdentityProvider = XMock::of(MotIdentityProvider::class);
        $this->cookieService = XMock::of(WebAuthenticationCookieService::class);
    }

    public function testGetState_givenNoToken_shouldSayNotAuthenticated_and_clearSession()
    {
        $this->tokenValueOnRequestIs(null);

        $this->assertEquals(new IdentitySessionState(false, true), $this->createService()->getState());
    }

    public function testGetState_givenInvalidToken_shouldSayNotAuthenticated_and_clearSession()
    {
        $token = 'g09erg90re09gre9g';
        $this->tokenValueOnRequestIs($token);
        $this->openAMTokenValidationResponseIs($token, false);

        $this->assertEquals(new IdentitySessionState(false, true), $this->createService()->getState());
    }

    public function testGetState_givenValidToken_and_noIdentity_shouldSayAuthenticated_and_clearSession()
    {
        $token = 'g09erg90re09gre9g';
        $this->tokenValueOnRequestIs($token);
        $this->openAMTokenValidationResponseIs($token, true);

        $this->assertEquals(new IdentitySessionState(true, true), $this->createService()->getState());
    }

    public function testGetState_givenValidToken_and_identityWithDifferentToken_shouldSayAuthenticated_and_clearSession(
    ) {
        $token = 'g09erg90re09gre9g';
        $tokenOnIdentity = 'zzzzzzzzzzzz';
        $this->tokenValueOnRequestIs($token);
        $this->openAMTokenValidationResponseIs($token, true);
        $this->identityExistsWithToken($tokenOnIdentity);

        $this->assertEquals(new IdentitySessionState(true, true), $this->createService()->getState());
    }

    public function testGetState_givenValidToken_and_identityWithIdenticalToken_shouldSayAuthenticated_and_sessionValid(
    ) {
        $token = 'g09erg90re09gre9g';

        $this->tokenValueOnRequestIs($token);
        $this->openAMTokenValidationResponseIs($token, true);
        $this->identityExistsWithToken($token);

        $this->assertEquals(new IdentitySessionState(true, false), $this->createService()->getState());
    }

    private function openAMTokenValidationResponseIs($token, $isValid)
    {
        $this->client->expects($this->once())->method('isTokenValid')->with($token)->willReturn($isValid);
    }

    private function identityExistsWithToken($token)
    {
        $this->motIdentityProvider->expects($this->atLeastOnce())->method('getIdentity')->
        willReturn((new \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity())->setAccessToken($token));
    }

    private function tokenValueOnRequestIs($token)
    {
        $this->cookieService->expects($this->atLeastOnce())->method('getToken')
            ->willReturn($token);
    }

    /**
     * @return IdentitySessionStateService
     */
    private function createService()
    {
        return new IdentitySessionStateService($this->client, $this->motIdentityProvider, $this->cookieService,
            XMock::of(Logger::class));
    }
}
