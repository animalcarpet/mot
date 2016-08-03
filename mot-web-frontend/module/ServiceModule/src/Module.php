<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\ServiceModule;

/**
 * Class Module
 */
class Module
{
    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../config/services.config.php';
    }
}
