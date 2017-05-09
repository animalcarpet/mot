<?php

namespace TestSupport\Helper;

use TestSupport\FieldValidation;

/**
 * Parses requestor username and password from the request payload.
 */
class RequestorParserHelper
{
    /**
     * @param array $data
     *
     * @return array count = 2; 0 -> (string) username, 1 -> (string) password
     */
    public static function parse($data)
    {
        FieldValidation::checkForRequiredFieldsInData(['requestor'], $data);
        $requestorData = $data['requestor'];
        FieldValidation::checkForRequiredFieldsInData(['username', 'password'], $requestorData);

        return [$requestorData['username'], $requestorData['password']];
    }
}
