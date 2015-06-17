<?php

namespace UserApiTest\Dashboard\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use UserApi\Dashboard\Dto\DayStats;

/**
 * Unit tests for DayStats dto
 */
class DayStatsTest extends \PHPUnit_Framework_TestCase
{
    public function testDto()
    {
        $total = 1;
        $numberOfPasses = 2;
        $numberOfFails = 3;
        $numberOfRetests = 4;

        $stats = new DayStats(
            $total,
            $numberOfPasses,
            $numberOfFails,
            $numberOfRetests
        );

        $return = $stats->toArray();
        $this->assertEquals($total, $return['total']);
        $this->assertEquals($numberOfPasses, $return['numberOfPasses']);
        $this->assertEquals($numberOfFails, $return['numberOfFails']);
        $this->assertEquals($numberOfRetests, $return['numberOfRetests']);
    }
}
