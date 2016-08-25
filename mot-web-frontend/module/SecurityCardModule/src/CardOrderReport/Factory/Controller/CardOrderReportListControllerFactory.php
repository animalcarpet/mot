<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Factory\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderReportListAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller\CardOrderReportListController;
use Zend\Http\Request;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardOrderReportListControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return CardOrderReportListController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $action = $serviceLocator->get(CardOrderReportListAction::class);
        return new CardOrderReportListController($action);
    }
}
