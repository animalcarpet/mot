<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaMotApi\Controller\ReasonForRejectionController;
use DvsaMotApi\Service\TestItemSelectorService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for the ReasonForRejectionController.
 */
class ReasonForRejectionControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return ReasonForRejectionController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $testItemSelectorService = $serviceLocator->get(TestItemSelectorService::class);
        $motTestService = $serviceLocator->get("MotTestService");

        return new ReasonForRejectionController(
            $testItemSelectorService,
            $motTestService
        );
    }
}
