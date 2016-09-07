<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Model;

use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardSerialNumberValidationCallback;

class GtmSecurityCardSerialNumberValidationCallback implements SecurityCardSerialNumberValidationCallback
{
    private $data = [];

    public function onInvalidFormat()
    {
        $this->data[] = [
            'event' => 'activate-card-fail',
            'reason' => 'invalid-serial-number',
            'title' => 'User - Activate Security Card'
        ];
    }

    public function toGtmData()
    {
        return $this->data;
    }
}
