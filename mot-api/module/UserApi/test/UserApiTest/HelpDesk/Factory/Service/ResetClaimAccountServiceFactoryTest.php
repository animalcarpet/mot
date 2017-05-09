<?php

namespace UserApiTest\HelpDesk\Factory\Service;

use AccountApi\Service\OpenAmIdentityService;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use DvsaEventApi\Service\EventService;
use MailerApi\Service\MailerService;
use UserApi\HelpDesk\Factory\Service\ResetClaimAccountServiceFactory;
use UserApi\HelpDesk\Service\ResetClaimAccountService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ResetClaimAccountServiceFactoryTest.
 */
class ResetClaimAccountServiceFactoryTest extends AbstractServiceTestCase
{
    public function testEventServiceGetList()
    {
        $serviceManager = new ServiceManager();

        $mockEntityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $mockEntityManager);

        $repo = XMock::of(PersonRepository::class);
        $this->mockMethod($mockEntityManager, 'getRepository', $this->once(), $repo);

        $mailer = XMock::of(MailerService::class);
        $serviceManager->setService(MailerService::class, $mailer);

        $openAm = XMock::of(OpenAmIdentityService::class);
        $serviceManager->setService(OpenAmIdentityService::class, $openAm);

        $event = XMock::of(EventService::class);
        $serviceManager->setService(EventService::class, $event);

        $auth = XMock::of(AuthorisationServiceInterface::class);
        $serviceManager->setService('DvsaAuthorisationService', $auth);

        $serviceManager->setService('config', []);

        $factory = new ResetClaimAccountServiceFactory();

        $this->assertInstanceOf(
            ResetClaimAccountService::class,
            $factory->createService($serviceManager)
        );
    }
}
