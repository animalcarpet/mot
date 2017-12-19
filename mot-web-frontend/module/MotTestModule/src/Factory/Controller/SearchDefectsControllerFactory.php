<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Controller;

use Dvsa\Mot\Frontend\MotTestModule\Controller\SearchDefectsController;
use Dvsa\Mot\Frontend\MotTestModule\Service\SearchReasonForRejectionService;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating SearchDefectsController instances.
 */
class SearchDefectsControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SearchDefectsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $mainServiceManager */
        $mainServiceManager = $serviceLocator->getServiceLocator();
        /** @var SearchReasonForRejectionService $searchReasonForRejectionService */
        $searchReasonForRejectionService = $mainServiceManager->get(SearchReasonForRejectionService::class);
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $mainServiceManager->get('Feature\FeatureToggles');

        return new SearchDefectsController($searchReasonForRejectionService, $featureToggles);
    }
}
