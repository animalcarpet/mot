<?php

namespace UserApi\Factory;

use UserApi\SpecialNotice\Controller\SpecialNoticeOverdueController;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SpecialNoticeOverdueControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $specialNoticeService = $serviceLocator->get(SpecialNoticeService::class);

        return new SpecialNoticeOverdueController($specialNoticeService);
    }
}
