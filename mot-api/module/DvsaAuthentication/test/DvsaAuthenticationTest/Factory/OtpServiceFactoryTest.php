<?php

namespace DvsaAuthenticationTest\Factory;

use Doctrine\ORM\EntityManager;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaAuthentication\Factory\OtpServiceFactory;
use DvsaAuthentication\Service\OtpService;
use DvsaEntities\Repository\ConfigurationRepositoryInterface;
use DvsaEntities\Repository\PersonRepository;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OtpServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $serviceLocator;

    protected function setUp()
    {
        $repository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        $this->serviceLocator = $this->getServiceLocator([
            EntityManager::class => $entityManager,
            'DvsaAuthenticationService' => $this->getMock(AuthenticationService::class),
            'ConfigurationRepository' => $this->getMock(ConfigurationRepositoryInterface::class),
            OpenAMClientInterface::class => $this->getMock(OpenAMClientInterface::class),
        ]);
    }

    public function testItIsAZendFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new OtpServiceFactory());
    }

    public function testItCreatesAnOtpService()
    {
        $factory = new OtpServiceFactory();

        $otpService = $factory->createService($this->serviceLocator);

        $this->assertInstanceOf(OtpService::class, $otpService);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceLocator(array $services)
    {
        $serviceLocator = $this->getMock(ServiceLocatorInterface::class);

        $serviceLocator->expects($this->any())
            ->method('get')
            ->with(call_user_func_array([$this, 'logicalOr'], array_keys($services)))
            ->will($this->returnCallback(function ($serviceName) use ($services) {
                return $services[$serviceName];
            }));

        return $serviceLocator;
    }
}
