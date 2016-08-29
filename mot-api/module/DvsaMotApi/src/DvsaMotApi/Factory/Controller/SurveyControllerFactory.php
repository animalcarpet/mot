<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaMotApi\Controller\SurveyController;
use DvsaMotApi\Service\SurveyService;
use Zend\Di\ServiceLocator;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SurveyControllerFactory.
 */
class SurveyControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface|ControllerManager $serviceLocator
     *
     * @return SurveyController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocator $serviceLocator */
        $mainServiceLocator = $serviceLocator->getServiceLocator();

        $surveyService = $mainServiceLocator->get(SurveyService::class);

        return new SurveyController($surveyService);
    }
}
