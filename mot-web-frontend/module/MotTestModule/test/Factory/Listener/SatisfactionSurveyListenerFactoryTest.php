<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */
namespace DvsaMotTestTest\Factory\Listener;

use Core\Service\MotEventManager;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Service\SurveyService;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Listener\SatisfactionSurveyListenerFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceManager;

class SatisfactionSurveyListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceIsCreated()
    {
        $serviceManager = new ServiceManager();

        $motEventManagerMock = $this->getMockBuilder(MotEventManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $surveyServiceMock = $this->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $featureToggleMock = $this->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $routerMock = $this->getMockBuilder(RouteStackInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService(MotEventManager::class, $motEventManagerMock);
        $serviceManager->setService(SurveyService::class, $surveyServiceMock);
        $serviceManager->setService('Feature\FeatureToggles', $featureToggleMock);
        $serviceManager->setService('Router', $routerMock);

        $listenerFactory = new SatisfactionSurveyListenerFactory();
        $listenerFactory->createService($serviceManager);
    }
}
