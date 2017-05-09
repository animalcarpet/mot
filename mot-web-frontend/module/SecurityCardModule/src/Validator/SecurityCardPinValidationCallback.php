<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Validator;

interface SecurityCardPinValidationCallback
{
    public function onInvalidLength();

    public function onNonNumeric();

    public function onBlankPin();
}
