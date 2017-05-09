<?php

namespace AccountTest\Factory\Service;

use DvsaCommonTest\TestUtils\XMock;
use Account\Service\ExpiredPasswordService;
use Account\Factory\Service\ExpiredPasswordServiceFactory;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaClient\Mapper\ExpiredPasswordMapper;
use DvsaCommon\Configuration\MotConfig;
use Core\Service\MotFrontendIdentityProviderInterface;
use Zend\ServiceManager\ServiceManager;

class ExpiredPasswordServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService('MotIdentityProvider', XMock::of(MotFrontendIdentityProviderInterface::class));
        $serviceManager->setService(MotConfig::class, XMock::of(MotConfig::class));
        $serviceManager->setService(ExpiredPasswordMapper::class, XMock::of(ExpiredPasswordMapper::class));
        $serviceManager->setService(OpenAMClientInterface::class, XMock::of(OpenAMClientInterface::class));

        $openAMClientOptions = XMock::of(OpenAMClientOptions::class);
        $openAMClientOptions
            ->expects($this->any())
            ->method('getRealm')
            ->willReturn('/realm');

        $serviceManager->setService(OpenAMClientOptions::class, $openAMClientOptions);

        // Create the factory
        $factory = new ExpiredPasswordServiceFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(ExpiredPasswordService::class, $factoryResult);
    }
}
