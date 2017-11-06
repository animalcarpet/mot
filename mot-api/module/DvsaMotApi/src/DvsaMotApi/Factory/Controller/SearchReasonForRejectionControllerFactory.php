<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaMotApi\Controller\SearchReasonForRejectionController;
use DvsaMotApi\Service\ReasonForRejection\SearchReasonForRejectionService;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for the ReasonForRejectionController.
 */
class SearchReasonForRejectionControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SearchReasonForRejectionController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var SearchReasonForRejectionService $searchReasonForRejectionService */
        $searchReasonForRejectionService = $serviceLocator->get(SearchReasonForRejectionService::class);

        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new SearchReasonForRejectionController($searchReasonForRejectionService, $featureToggles);
    }
}
