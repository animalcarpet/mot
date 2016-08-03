<?php

/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\ServiceModule\Factory;

use Dvsa\Mot\Api\ServiceModule\Model\ApiServicesConfigOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class ApiServicesConfigOptionsFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $apiConfig = $serviceLocator->get('config');
        $apiConfig = $config['api'];
        return new ApiServicesConfigOptions($apiConfig);
    }
}
