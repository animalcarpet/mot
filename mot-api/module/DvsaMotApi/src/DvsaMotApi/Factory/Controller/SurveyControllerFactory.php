<?php

namespace DvsaMotApi\Factory\Controller;

use Doctrine\ORM\EntityManager;
use DvsaMotApi\Controller\SurveyController;
use DvsaMotApi\Service\SurveyService;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SurveyControllerFactory.
 */
class SurveyControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SurveyController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocator $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        $surveyService = $serviceLocator->get(SurveyService::class);

        return new SurveyController(
            $surveyService
        );
    }
}
