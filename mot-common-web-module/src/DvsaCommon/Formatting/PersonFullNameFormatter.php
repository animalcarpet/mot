<?php

namespace DvsaCommon\Formatting;

class PersonFullNameFormatter
{
    public function format($firstName, $middleName, $familyName)
    {
        return join(' ', array_filter([$firstName, $middleName, $familyName]));
    }
}
