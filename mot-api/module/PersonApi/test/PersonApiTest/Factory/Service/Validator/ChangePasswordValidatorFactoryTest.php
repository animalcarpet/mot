<?php

namespace PersonApiTest\Factory\Service\Validator;

use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use PersonApi\Service\Validator\ChangePasswordValidator;
use PersonApi\Factory\Service\Validator\ChangePasswordValidatorFactory;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use Dvsa\OpenAM\OpenAMClientInterface;
use Zend\ServiceManager\ServiceManager;

class ChangePasswordValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(MotIdentityProviderInterface::class, $this->createIdentityProvider());

        $service = XMock::of(OpenAMClientOptions::class);
        $serviceManager->setService(OpenAMClientOptions::class, $service);

        $service = XMock::of(OpenAMClientInterface::class);
        $serviceManager->setService(OpenAMClientInterface::class, $service);

        // Create the factory
        $factory = new ChangePasswordValidatorFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(ChangePasswordValidator::class, $factoryResult);
    }

    private function createIdentityProvider()
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('tester');

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        return $identityProvider;
    }
}
