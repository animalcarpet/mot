<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Site\Factory\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Organisation\Controller\MotTestLogController;
use Organisation\Factory\Controller\MotTestLogControllerFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class MotTestLogControllerFactoryTest.
 */
class MotTestLogControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $serviceManager->setService('AuthorisationService', $authorisationService);

        $mapperFactory = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $mapperFactory);

        $featureToggles = XMock::of(FeatureToggles::class);
        $serviceManager->setService('Feature\FeatureToggles', $featureToggles);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        $this->assertInstanceOf(
            MotTestLogController::class,
            (new MotTestLogControllerFactory())->createService($plugins)
        );
    }
}
