<?php


namespace DvsaCommon\Model;


use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

class AuthorisationForTestingMotStatus
{
    /** get Possible Test Quality Information array with Status Codes for PersonProfile */
    public static function getPossibleStatusesForTqiAssertion()
    {
        return [
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            AuthorisationForTestingMotStatusCode::SUSPENDED,
        ];
    }
}