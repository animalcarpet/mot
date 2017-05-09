<?php

namespace Application\Factory\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Service\ReportBuilder\Service as ReportBuilderService;

class ReportBuilderServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //quick fix, allows running unit tests from the console
        //getQuery not available in Zend\Console\Request
        $request = $serviceLocator->get('Request');

        if (method_exists($request, 'getQuery')) {
            $params = $request->getQuery();
        } else {
            $params = new \Zend\Stdlib\Parameters();
        }

        $reportBuilderService = new ReportBuilderService(
            $params,
            $serviceLocator->get('Config')
        );

        return $reportBuilderService;
    }
}
