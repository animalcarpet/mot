<?php

namespace VehicleApiTest\MysteryShopper;

use VehicleApi\MysteryShopper\CampaignDates;

/**
 * Class CampaignDates.
 */
class CampaignDatesTest extends \PHPUnit_Framework_TestCase
{
    const START = '1978-03-21';
    const END = '1978-03-22';
    const LAST_TEST = '1978-03-23';

    public function testCampaignDates()
    {
        $campaignDuration = new CampaignDates(self::START, self::END, self::LAST_TEST);

        foreach ([
                     'getStart' => 'START',
                     'getEnd' => 'END',
                     'getLastTest' => 'LAST_TEST',
                 ] as $getter => $constantName) {
            $constant = constant('self::'.$constantName);

            $this->assertEquals(
                $constant,
                $campaignDuration->$getter()
            );

            $this->assertInstanceOf(
                \DateTime::class,
                $campaignDuration->$getter(true)
            );

            $this->assertEquals(
                $constant,
                $campaignDuration->$getter()
            );
        }
    }
}
