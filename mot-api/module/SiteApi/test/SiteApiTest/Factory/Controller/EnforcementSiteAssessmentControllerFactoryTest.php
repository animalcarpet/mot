<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\EnforcementSiteAssessmentController;
use SiteApi\Factory\Controller\EnforcementSiteAssessmentControllerFactory;
use SiteApi\Service\EnforcementSiteAssessmentService;
use Zend\ServiceManager\ServiceManager;

class EnforcementSiteAssessmentControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $enforcementSiteAssessmentService = XMock::of(EnforcementSiteAssessmentService::class);
        $serviceManager->setService(EnforcementSiteAssessmentService::class, $enforcementSiteAssessmentService);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new EnforcementSiteAssessmentControllerFactory($serviceManager);
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(EnforcementSiteAssessmentController::class, $factoryResult);
    }
}
