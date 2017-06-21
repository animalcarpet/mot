<?php

namespace DvsaCommonApiTest\Factory\Filter;

use DvsaCommonApi\Factory\Filter\XssFilterFactory;
use DvsaCommonApi\Filter\XssFilter;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SpecialNoticesControllerFactoryTest.
 */
class XssFilterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsXssFilterInstance()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('HTMLPurifier', $this->getMockBuilder('HTMLPurifier')->disableOriginalConstructor()->getMock());
        $factory = new XssFilterFactory();

        $this->assertInstanceOf(XssFilter::class, $factory->createService($serviceManager));
    }
}
