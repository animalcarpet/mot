<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service;

use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationAccountLockoutViewModelBuilderFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @throws RuntimeException If helpdesk details are not found in the configuration
     *
     * @return AuthenticationAccountLockoutViewModelBuilder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $helpdeskConfig = isset($config['helpdesk']) ? $config['helpdesk'] : null;
        if (!$helpdeskConfig) {
            throw new RuntimeException('Helpdesk details not found in $config["helpdesk"]');
        }

        return new AuthenticationAccountLockoutViewModelBuilder($helpdeskConfig);
    }
}
