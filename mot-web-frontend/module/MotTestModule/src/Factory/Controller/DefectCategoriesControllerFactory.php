<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use DvsaAuthorisation\Service\AuthorisationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating DefectCategoriesController instances.
 */
class DefectCategoriesControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DefectCategoriesController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();

        /** @var AuthorisationService $authorisationService */
        $authorisationService = $mainServiceManager->get('AuthorisationService');
        /**
         * @var DefectsContentBreadcrumbsBuilder
         */
        $breadcrumbsBuilder = $mainServiceManager->get(DefectsContentBreadcrumbsBuilder::class);

        return new DefectCategoriesController($authorisationService, $breadcrumbsBuilder);
    }
}
