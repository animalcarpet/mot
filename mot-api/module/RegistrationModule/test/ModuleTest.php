<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest;

use Dvsa\Mot\Api\RegistrationModule\Module;
use PHPUnit_Framework_TestCase;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $module = new Module();
        $config = $module->getConfig();

        $this->assertInternalType('array', $config);

        $this->assertSame($config, unserialize(serialize($config)));
    }

    public function testGetControllerConfig()
    {
        $module = new Module();
        $config = $module->getControllerConfig();

        $this->assertInternalType('array', $config);
    }

    public function testGetServiceConfig()
    {
        $module = new Module();
        $config = $module->getServiceConfig();

        $this->assertInternalType('array', $config);
    }
}
