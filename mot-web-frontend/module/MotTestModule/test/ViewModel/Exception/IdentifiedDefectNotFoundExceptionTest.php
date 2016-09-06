<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel\Exception;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Exception\IdentifiedDefectNotFoundException;
use PHPUnit_Framework_TestCase;

class IdentifiedDefectNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testMessageGetsInitialised()
    {
        $exception = new IdentifiedDefectNotFoundException(666);
        $this->assertEquals('Unable to find IdentifiedDefect with id "666".', $exception->getMessage());
    }
}
