<?php

namespace DvsaMotTest\Factory\Controller;

use DvsaMotTest\Action\BrakeTestResults\SubmitBrakeTestConfigurationAction;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Controller\BrakeTestResultsController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BrakeTestResultsControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return BrakeTestResultsController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var SubmitBrakeTestConfigurationAction $submitBrakeTestConfigurationAction */
        $submitBrakeTestConfigurationAction = $serviceLocator->get(SubmitBrakeTestConfigurationAction::class);

        /** @var ViewBrakeTestConfigurationAction $viewBrakeTestConfigurationAction */
        $viewBrakeTestConfigurationAction = $serviceLocator->get(ViewBrakeTestConfigurationAction::class);

        return new BrakeTestResultsController($submitBrakeTestConfigurationAction, $viewBrakeTestConfigurationAction);
    }
}
