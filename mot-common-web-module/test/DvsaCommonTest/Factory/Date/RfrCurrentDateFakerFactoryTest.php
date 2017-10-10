<?php

namespace DvsaCommonTest\Factory\Date;

use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Date\RfrCurrentDateFaker;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Factory\Date\RfrCurrentDateFakerFactory;
use \PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\ServiceManager\ServiceLocatorInterface;

class RfrCurrentDateFakerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MotConfig */
    private $config;

    /** @var  ServiceLocatorInterface | MockObj */
    private $serviceLocator;

    /** @var  RfrCurrentDateFakerFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new RfrCurrentDateFakerFactory();
    }

    private function withConfig(array $configValues = [])
    {
        $this->config = new MotConfig($configValues);

        $this->serviceLocator = XMock::of(ServiceLocatorInterface::class);

        $this->serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with(MotConfig::class)
            ->willReturn($this->config);
    }

    public function testFactory_withNoParameterSet_shouldDefaultToNull()
    {
        $this->withConfig();

        $result = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf(RfrCurrentDateFaker::class, $result);
        $this->assertNull($this->factory->getFakeDateValue($this->config));
    }
    
    /**
     * @dataProvider invalidParamValueDP
     */
    public function testFactory_withInvalidParam_shouldDefaultToNull($paramValue)
    {
        $this->withConfig([
            RfrCurrentDateFakerFactory::DATE_CONFIG_KEY => $paramValue
        ]);

        $result = $this->factory->createService($this->serviceLocator);

        $this->assertInstanceOf(RfrCurrentDateFaker::class, $result);
        $this->assertNull($this->factory->getFakeDateValue($this->config));
    }

    public function invalidParamValueDP()
    {
        return [
            [ 1233 ],
            [ 3.1415 ],
            [ [] ],
            [ [[]] ],
            [ '    ' ],
            [ 'adfasdfas' ],
            [ null ],
            [ new \stdClass() ]
        ];
    }

    /**
     * @dataProvider validParamDP
     */
    public function testFactory_witValidParam($paramValue)
    {
        $expectedDate = new \DateTime($paramValue);
        $this->withConfig([
            RfrCurrentDateFakerFactory::DATE_CONFIG_KEY => $paramValue
        ]);

        $result = $this->factory->createService($this->serviceLocator);
        $fakeDateResult = $this->factory->getFakeDateValue($this->config);

        $this->assertInstanceOf(RfrCurrentDateFaker::class, $result);
        $this->assertInstanceOf('\DateTime', $fakeDateResult);
        $this->assertEquals($expectedDate, $fakeDateResult);
    }

    public function validParamDP()
    {
        return [
            ['2018-01-01'],
            ['now'],
            ['tomorrow'],
            ['+1 year'],
        ];
    }
}