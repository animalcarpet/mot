<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTestTest\Factory\Listener;

use Core\Service\MotEventManager;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Listener\SatisfactionSurveyListenerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaApplicationLogger\Log\Logger;
use DvsaFeature\FeatureToggles;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceManager;

class SatisfactionSurveyListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceIsCreated()
    {
        $serviceManager = new ServiceManager();

        $motEventManagerMock = $this
            ->getMockBuilder(MotEventManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $surveyServiceMock = $this
            ->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $featureToggleMock = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $routerMock = $this->getMockBuilder(RouteStackInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService(MotEventManager::class, $motEventManagerMock);
        $serviceManager->setService(SurveyService::class, $surveyServiceMock);
        $serviceManager->setService('Feature\FeatureToggles', $featureToggleMock);
        $serviceManager->setService('Router', $routerMock);
        $serviceManager->setService('Application\Logger', $logger);

        $listenerFactory = new SatisfactionSurveyListenerFactory();
        $listenerFactory->createService($serviceManager);
    }
}
