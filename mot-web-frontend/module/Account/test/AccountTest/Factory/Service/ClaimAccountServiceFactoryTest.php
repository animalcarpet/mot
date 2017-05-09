<?php

namespace AccountTest\Factory\Service;

use Account\Factory\Service\ClaimAccountServiceFactory;
use Account\Service\ClaimAccountService;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClaimAccountServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testEventServiceGetList()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);

        $mockMotIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->mockMethod(
            $mockMotIdentityProvider, 'getIdentity', $this->once(), XMock::of(MotFrontendIdentityInterface::class)
        );

        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $mockMotIdentityProvider);
        $this->mockMethod(
            $mockServiceLocator, 'get', $this->at(1), XMock::of(MotFrontendAuthorisationServiceInterface::class)
        );
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(MapperFactory::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(ParamObfuscator::class));

        $factory = new ClaimAccountServiceFactory();

        $this->assertInstanceOf(
            ClaimAccountService::class,
            $factory->createService($mockServiceLocator)
        );
    }
}
