<?php

namespace DvsaAuthenticationTest\Authentication\Adapter\OpenAM;

use DvsaAuthentication\Authentication\Adapter\OpenAM\OpenAMApiTokenBasedAdapter;
use DvsaAuthentication\Identity;
use DvsaAuthentication\Service\ApiTokenService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use Zend\Authentication\Result;
use Zend\Log\LoggerInterface;

class OpenAMApiTokenBasedAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $tokenService;

    private $identityByTokenResolver;

    private $logger;

    public function setUp()
    {
        $this->tokenService = XMock::of(ApiTokenService::class);
        $this->identityByTokenResolver = XMock::of(Identity\OpenAM\OpenAMIdentityByTokenResolver::class);
        $this->logger = XMock::of(LoggerInterface::class);
    }

    public function testAuthenticate_parseTokenFailed_shouldReceiveFailureCredentialsInvalidResult()
    {
        $token = null;
        $this->tokenService->expects($this->atLeastOnce())->method('parseToken')->willReturn($token);

        $result = $this->createAdapter()->authenticate();

        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }

    public function testAuthenticate_identityResolutionFailed()
    {
        $token = 'toooken';
        $this->tokenService->expects($this->atLeastOnce())->method('parseToken')->willReturn($token);
        $this->identityByTokenResolver->expects($this->once())->method('resolve')->willReturn(null);

        $result = $this->createAdapter()->authenticate();

        $this->assertEquals(Result::FAILURE, $result->getCode());
    }

    public function testAuthenticate_successResolution()
    {
        $token = 'toooken';

        $person = new Identity((new Person()));

        $this->tokenService->expects($this->atLeastOnce())->method('parseToken')->willReturn($token);
        $this->identityByTokenResolver->expects($this->once())->method('resolve')
            ->willReturn($person);

        $result = $this->createAdapter()->authenticate();

        $this->assertEquals(Result::SUCCESS, $result->getCode());
    }

    private function createAdapter()
    {
        return new OpenAMApiTokenBasedAdapter($this->identityByTokenResolver, $this->logger, $this->tokenService);
    }
}
