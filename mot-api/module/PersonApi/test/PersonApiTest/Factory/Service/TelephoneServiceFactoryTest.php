<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Factory\Service\TelephoneServiceFactory;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;
use PersonApi\Service\TelephoneService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PersonServiceFactoryTest.
 */
class TelephoneServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(ContactDetailsService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(AuthorisationService::class));
        $this->mockMethod(
            $mockServiceLocator,
            'get',
            $this->at(3),
            XMock::of(PersonDetailsChangeNotificationHelper::class)
        );
        $this->assertInstanceOf(
            TelephoneService::class,
            (new TelephoneServiceFactory())->createService($mockServiceLocator)
        );
    }
}
