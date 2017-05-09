<?php

namespace PersonApiTest\Factory\Service;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Service\Validator\ChangePasswordValidator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use AccountApi\Service\OpenAmIdentityService;
use PersonApi\Service\PasswordService;
use PersonApi\Factory\Service\PasswordServiceFactory;
use Zend\ServiceManager\ServiceManager;

class PasswordServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(MotIdentityProviderInterface::class, XMock::of(MotIdentityProviderInterface::class));
        $serviceManager->setService(ChangePasswordValidator::class, XMock::of(ChangePasswordValidator::class));
        $serviceManager->setService(OpenAmIdentityService::class, XMock::of(OpenAmIdentityService::class));

        // Create the factory
        $factory = new PasswordServiceFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(PasswordService::class, $factoryResult);
    }
}
