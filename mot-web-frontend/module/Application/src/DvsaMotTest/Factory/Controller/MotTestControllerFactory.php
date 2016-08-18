<?php

namespace DvsaMotTest\Factory\Controller;

use Application\View\Helper\AuthorisationHelper;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Service\SurveyService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
class MotTestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return MotTestController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var SurveyService $surveyService */
        $surveyService = $serviceLocator->get(SurveyService::class);

        /** @var AuthorisationHelper $authService */
        $authService = $serviceLocator->get('authorisationHelper');


        return new MotTestController($authService, $surveyService);
    }
}
