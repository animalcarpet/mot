<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\GenderRepository;
use DvsaEntities\Repository\TitleRepository;
use DvsaEntities\Repository\AuthenticationMethodRepository;
use PersonApi\Factory\Service\BasePersonServiceFactory;
use PersonApi\Service\BasePersonService;
use PersonApi\Service\Validator\BasePersonValidator;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class BasePersonServiceFactoryTest.
 */
class BasePersonServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(TitleRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(GenderRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(2), XMock::of(AuthenticationMethodRepository::class));

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(BasePersonValidator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(ContactDetailsService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(XssFilter::class));

        $this->assertInstanceOf(
            BasePersonService::class,
            (new BasePersonServiceFactory())->createService($mockServiceLocator)
        );
    }
}
