<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaMotApi\Controller\TestItemSelectorController;
use DvsaMotApi\Service\TestItemSelectorService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for the TestItemSelectorController.
 */
class TestItemSelectorControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return TestItemSelectorController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $testItemSelectorService = $serviceLocator->get(TestItemSelectorService::class);
        $motTestService = $serviceLocator->get("MotTestService");

        return new TestItemSelectorController(
            $testItemSelectorService,
            $motTestService
        );
    }
}
