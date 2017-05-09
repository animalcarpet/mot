<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\EnforcementSiteAssessmentController;
use SiteApi\Service\EnforcementSiteAssessmentService;
use Zend\ServiceManager\ServiceManager;
use SiteApi\Factory\Controller\EnforcementSiteAssessmentControllerFactory;

/**
 * Class SiteRiskControllerFactoryTest.
 */
class EnforcementSiteControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $enforcementService = XMock::of(EnforcementSiteAssessmentService::class);
        $serviceManager->setService(EnforcementSiteAssessmentService::class, $enforcementService);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new EnforcementSiteAssessmentControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(EnforcementSiteAssessmentController::class, $factoryResult);
    }
}
