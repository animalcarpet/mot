<?php

namespace DvsaMotApiTest\Dto;

use DvsaMotApi\Service\Validator\BrakeResult;
use PHPUnit_Framework_TestCase;

/**
 * Class BrakeResultTest
 */
class BrakeResultTest extends PHPUnit_Framework_TestCase
{
    public function testFromDataArrayMethodMapsCorrectlyToDto()
    {
        $values = [
            'control' => 'control one',
            'location' => 'front',
            'effort' => 100,
            'lock' => true,
        ];

        $brakeResult = (
            new BrakeResult())
                ->setControl($values['control'])
                ->setLocation($values['location'])
                ->setEffort($values['effort'])
                ->setLocked($values['lock']
            );

        $this->assertEquals($values['control'], $brakeResult->getControl());
        $this->assertEquals($values['location'], $brakeResult->getLocation());
        $this->assertEquals($values['effort'], $brakeResult->getEffort());
        $this->assertEquals($values['lock'], $brakeResult->isLocked());
    }
}
