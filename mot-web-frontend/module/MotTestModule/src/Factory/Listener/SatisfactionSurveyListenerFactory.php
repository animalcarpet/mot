<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Listener;

use Core\Service\MotEventManager;
use Dvsa\Mot\Frontend\MotTestModule\Listener\SatisfactionSurveyListener;
use DvsaMotTest\Service\SurveyService;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SatisfactionSurveyListenerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SatisfactionSurveyListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var SurveyService $surveyService */
        $surveyService = $serviceLocator->get(SurveyService::class);

        /** @var MotEventManager $eventManager */
        $eventManager = $serviceLocator->get(MotEventManager::class);

        /** @var RouteStackInterface $router */
        $router = $serviceLocator->get('Router');

        return new SatisfactionSurveyListener($surveyService, $eventManager, $router);
    }
}
