<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderCsvReportAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller\CardOrderCsvReportController;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderCsvReportControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $action = $serviceLocator->get(CardOrderCsvReportAction::class);

        return new CardOrderCsvReportController($action);
    }
}
