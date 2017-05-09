<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntitiesTest\Entity;

use DateTime;
use DvsaEntities\Entity\Survey;
use PHPUnit_Framework_TestCase;

class SurveyTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $survey = new Survey(5);
        $this->assertSame('Survey[id: null] rating: 5', (string) $survey);
    }

    public function testGetRating()
    {
        foreach ([null, 1, 5] as $rating) {
            $survey = new Survey($rating);
            $this->assertEquals($rating, $survey->getRating());
            unset($survey);
        }
    }

    public function testGetDate()
    {
        $survey = new Survey(null);
        $now = new DateTime();

        $createdOn = $survey->getCreatedOn();

        $this->assertInstanceOf(DateTime::class, $createdOn);
        // Checking if $currentDate is "today" can fail if this test executes exactly at midnight. As such we also allow
        // for "yesterday" as a valid scenario.
        $this->assertTrue($now->diff($createdOn)->days <= 1);
    }
}
