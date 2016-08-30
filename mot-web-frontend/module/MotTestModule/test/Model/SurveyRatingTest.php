<?php

namespace DvsaMotTestTest\Model;


use Dvsa\Mot\Frontend\MotTestModule\Model\SurveyRating;

class SurveyRatingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRatings()
    {
        $surveyRating = new SurveyRating();

        $this->assertEquals([
                'Very satisfied'                     => 5,
                'Satisfied'                          => 4,
                'Neither satisfied nor dissatisfied' => 3,
                'Dissatisfied'                       => 2,
                'Very dissatisfied'                  => 1,
            ],
            $surveyRating->getAll());
    }
}