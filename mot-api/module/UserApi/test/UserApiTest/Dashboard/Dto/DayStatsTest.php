<?php

namespace UserApiTest\Dashboard\Dto;

use UserApi\Dashboard\Dto\DayStats;

/**
 * Unit tests for DayStats dto.
 */
class DayStatsTest extends \PHPUnit_Framework_TestCase
{
    public function testDto()
    {
        $total = 1;
        $numberOfPasses = 2;
        $numberOfFails = 3;

        $stats = new DayStats(
            $total,
            $numberOfPasses,
            $numberOfFails
        );

        $return = $stats->toArray();
        $this->assertEquals($total, $return['total']);
        $this->assertEquals($numberOfPasses, $return['numberOfPasses']);
        $this->assertEquals($numberOfFails, $return['numberOfFails']);
    }
}
