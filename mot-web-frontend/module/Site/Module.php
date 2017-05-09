<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Site;

use Site\Factory\Service\SiteSearchServiceFactory;
use Site\Service\SiteSearchService;
use Site\UpdateVtsProperty\Factory\UpdateVtsPropertyProcessBuilderFactory;
use Site\UpdateVtsProperty\UpdateVtsPropertyProcessBuilder;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Site Module.
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                SiteSearchService::class => SiteSearchServiceFactory::class,
                UpdateVtsPropertyProcessBuilder::class => UpdateVtsPropertyProcessBuilderFactory::class,
            ],
        ];
    }
}
