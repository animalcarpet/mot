<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\EnforcementSiteAssessmentValidateController;
use SiteApi\Factory\Controller\EnforcementSiteAssessmentValidateControllerFactory;
use SiteApi\Service\EnforcementSiteAssessmentService;
use Zend\ServiceManager\ServiceManager;

class EnforcementSiteAssessmentValidateControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $enforcementSiteAssessmentService = XMock::of(EnforcementSiteAssessmentService::class);
        $serviceManager->setService(EnforcementSiteAssessmentService::class, $enforcementSiteAssessmentService);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new EnforcementSiteAssessmentValidateControllerFactory($serviceManager);
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(EnforcementSiteAssessmentValidateController::class, $factoryResult);
    }
}
