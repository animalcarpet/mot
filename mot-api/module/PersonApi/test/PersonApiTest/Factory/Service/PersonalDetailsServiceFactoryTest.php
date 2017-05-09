<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Factory\Service\PersonalDetailsServiceFactory;
use PersonApi\Service\PersonalDetailsService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PersonalDetailsServiceFactoryTest.
 */
class PersonalDetailsServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(MotIdentityProviderInterface::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(XssFilter::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(UserRoleService::class));

        $this->assertInstanceOf(
            PersonalDetailsService::class,
            (new PersonalDetailsServiceFactory())->createService($mockServiceLocator)
        );
    }
}
