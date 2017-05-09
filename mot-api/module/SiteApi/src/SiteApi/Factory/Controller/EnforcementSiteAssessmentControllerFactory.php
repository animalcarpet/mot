<?php

namespace SiteApi\Factory\Controller;

use SiteApi\Controller\EnforcementSiteAssessmentController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use SiteApi\Service\EnforcementSiteAssessmentService;

/**
 * Class EnforcementSiteAssessmentControllerFactory.
 */
class EnforcementSiteAssessmentControllerFactory implements FactoryInterface
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

        return new EnforcementSiteAssessmentController($serviceLocator->get(EnforcementSiteAssessmentService::class));
    }
}
