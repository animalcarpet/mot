<?php

namespace DvsaCommonTest\Factory\Validator;

use ApplicationTest\Crypt\Hash\MotConfigTest;
use Doctrine\Common\Cache\Cache;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Factory\HttpRestJson\ClientFactory;
use DvsaCommon\HttpRestJson\CachingClient;
use DvsaCommon\HttpRestJson\ZendClient;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $services = [];

    public function setUp()
    {
        $this->services = [
            'tokenService' => $this->getMockBuilder(TokenServiceInterface::class)->setMethods(['getToken'])->getMock(),
        ];
    }

    public function testItIsAZendSerivceManagerFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new ClientFactory());
    }

    public function testItCreatesTheZendClientByDefault()
    {
        $this->services['config'] = ['apiUrl' => 'localhost'];
        $this->services[MotConfig::class] = new MotConfig([]);
        $serviceLocator = $this->getServiceLocator($this->services);

        $factory = new ClientFactory();

        $this->assertInstanceOf(ZendClient::class, $factory->createService($serviceLocator));
    }

    public function testItCreatesTheCachingClientIfEnabled()
    {
        $this->services['config'] = [
            'apiUrl' => 'localhost',
            'rest_client' => [
                'cache' => [
                    'enabled' => true
                ]
            ]
        ];
        $this->services[MotConfig::class] = new MotConfig([]);
        $this->services[Cache::class] = $this->getMockBuilder(Cache::class)->getMock();

        $serviceLocator = $this->getServiceLocator($this->services);

        $factory = new ClientFactory();

        $this->assertInstanceOf(CachingClient::class, $factory->createService($serviceLocator));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceLocator(array $services)
    {
        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)->getMock();

        $serviceLocator->expects($this->any())
            ->method('get')
            ->with(call_user_func_array([$this, 'logicalOr'], array_keys($services)))
            ->will($this->returnCallback(function ($serviceName) use ($services) {
                return $services[$serviceName];
            }));

        return $serviceLocator;
    }
}