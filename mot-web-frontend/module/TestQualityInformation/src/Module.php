<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\TestQualityInformation;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * TestQualityInformation Module.
 */
class Module implements ConfigProviderInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }
}
