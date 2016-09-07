<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Model;

use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidationCallback;

class GtmSecurityCardPinValidationCallback implements SecurityCardPinValidationCallback
{
    private $data = [];

    public function onInvalidLength()
    {
        $this->data[] = [
            'event' => 'activate-card-fail',
            'reason' => 'invalid-pin',
            'title' => 'User - Activate Security Card'
        ];
    }

    public function onNonNumeric()
    {
        $this->data[] = [
            'event' => 'activate-card-fail',
            'reason' => 'invalid-pin',
            'title' => 'User - Activate Security Card'
        ];
    }

    public function onBlankPin()
    {
        $this->data[] = [
            'event' => 'activate-card-fail',
            'reason' => 'empty-pin',
            'title' => 'User - Activate Security Card'
        ];
    }

    public function toGtmData()
    {
        return $this->data;
    }
}
