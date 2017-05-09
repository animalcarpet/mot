<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Model;

use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidationCallback;

class GtmSecurityCardPinValidationCallback implements SecurityCardPinValidationCallback
{
    private $data;

    public function onInvalidLength()
    {
        $this->data = ['event' => 'user-login-failed', 'reason' => 'wrong-pin-length'];
    }

    public function onNonNumeric()
    {
        $this->data = ['event' => 'user-login-failed', 'reason' => 'invalid-pin'];
    }

    public function onBlankPin()
    {
        $this->data = ['event' => 'user-login-failed', 'reason' => 'empty-pin'];
    }

    public function toGtmData()
    {
        return $this->data;
    }
}
