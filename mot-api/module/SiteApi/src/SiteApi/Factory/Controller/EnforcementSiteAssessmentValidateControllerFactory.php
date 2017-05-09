<?php

namespace SiteApi\Factory\Controller;

use SiteApi\Controller\EnforcementSiteAssessmentController;
use SiteApi\Controller\EnforcementSiteAssessmentValidateController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use SiteApi\Service\EnforcementSiteAssessmentService;

/**
 * Class EnforcementSiteAssessmentValidateControllerFactory.
 */
class EnforcementSiteAssessmentValidateControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return EnforcementSiteAssessmentController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new EnforcementSiteAssessmentValidateController($serviceLocator->get(EnforcementSiteAssessmentService::class));
    }
}
