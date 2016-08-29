<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Model;

class SurveyRating
{
    private $ratings = [
        'Very satisfied'                     => 5,
        'Satisfied'                          => 4,
        'Neither satisfied nor dissatisfied' => 3,
        'Dissatisfied'                       => 2,
        'Very dissatisfied'                  => 1,
    ];

    public function getAll()
    {
        return $this->ratings;
    }
}
