<?php

namespace MailerApiTest\Factory\Logic;

use DvsaCommonTest\TestUtils\XMock;
use MailerApi\Factory\Logic\UserNameCreatorFactory;
use MailerApi\Logic\AbstractMailerLogic;
use MailerApi\Service\MailerService;
use MailerApi\Service\TemplateResolverService;
use Zend\ServiceManager\ServiceManager;
use MailerApi\Logic\UsernameCreator;

class UserNameCreatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(MailerService::class, XMock::of(MailerService::class));
        $serviceManager->setService(TemplateResolverService::class, XMock::of(TemplateResolverService::class));

        // Faking a config
        $config = [
            AbstractMailerLogic::CONFIG_KEY => [],
        ];
        $serviceManager->setService('config', $config);

        // Create the factory
        $factory = new UserNameCreatorFactory();
        $service = $factory->createService($serviceManager);

        $this->assertInstanceOf(
            UsernameCreator::class,
            $service
        );
    }
}
