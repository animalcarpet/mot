<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel\Exception\ObservedDefectNotFoundException;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Exception\ObservedDefectNotFoundException;
use PHPUnit_Framework_TestCase;

class ObservedDefectNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testMessageGetsInitialised()
    {
        $exception = new ObservedDefectNotFoundException(666);
        $this->assertEquals('Unable to find ObservedDefect with id "666".', $exception->getMessage());
    }
}
