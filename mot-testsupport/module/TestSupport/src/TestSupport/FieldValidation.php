<?php

namespace TestSupport;

/**
 * Originally copied from \DvsaCommonApi\Service\Exception\RequiredFieldException.
 */
class FieldValidation
{
    public static function checkForRequiredFieldsInData($requiredFieldNames, $data)
    {
        $missingFieldNames = [];
        foreach ($requiredFieldNames as $requiredFieldName) {
            if (!array_key_exists($requiredFieldName, $data) || is_null($data[$requiredFieldName])) {
                $missingFieldNames[] = $requiredFieldName;
            }
        }

        if (count($missingFieldNames) > 0) {
            throw new \Exception('Missing: '.print_r($missingFieldNames, true));
        }
    }
}
