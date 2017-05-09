<?php

namespace AccountTest\Factory\Service;

use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use Account\Factory\Service\PasswordResetServiceFactory;
use Account\Service\PasswordResetService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PasswordResetServiceFactoryTest.
 */
class PasswordResetServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /* @var PasswordResetServiceFactory $securityServiceFactory */
    private $serviceFactory;

    private $serviceLocatorMock;
    private $mapperMock;

    public function setUp()
    {
        $this->serviceFactory = new PasswordResetServiceFactory();
        $this->serviceLocatorMock = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mapperMock = XMock::of(MapperFactory::class);
    }

    public function testEventServiceGetList()
    {
        $this->serviceLocatorMock->expects($this->at(0))
            ->method('get')
            ->willReturn($this->mapperMock);
        $this->assertInstanceOf(
            PasswordResetService::class,
            $this->serviceFactory->createService($this->serviceLocatorMock)
        );
    }
}
