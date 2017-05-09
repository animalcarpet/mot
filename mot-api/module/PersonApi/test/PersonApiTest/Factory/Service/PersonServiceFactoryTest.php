<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Service\TesterService;
use PersonApi\Factory\Service\PersonServiceFactory;
use PersonApi\Service\PersonService;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PersonServiceFactoryTest.
 */
class PersonServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod(
            $entityManager,
            'getRepository',
            $this->at(0),
            XMock::of(
                PersonRepository::class
            )
        );

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(OpenAMClientOptions::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(OpenAMClientInterface::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(TesterService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(AuthorisationService::class));
        $this->mockMethod(
            $mockServiceLocator, 'get', $this->at(5), XMock::of(AuthenticationService::class)
        );
        $this->assertInstanceOf(
            PersonService::class,
            (new PersonServiceFactory())->createService($mockServiceLocator)
        );
    }
}
