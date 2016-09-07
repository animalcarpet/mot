<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Validator;

interface SecurityCardSerialNumberValidationCallback
{
    public function onInvalidFormat();
}
