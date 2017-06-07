<?php
namespace Dvsa\Mot\Behat\Support\Data\Params;

use DvsaCommon\Enum\LicenceCountryCode;
use DvsaCommon\Enum\PersonAuthType;
use TestSupport\Helper\DataGeneratorHelper;

class PersonParams
{
    const ID = "id";
    const USER_ID = "userId";
    const USERNAME = "username";
    const PASSWORD = "password";
    const REQUESTOR = "requestor";
    const USER_NAME = "userName";
    const FIRST_NAME = "firstName";
    const MIDDLE_NAME = "middleName";
    const SURNAME = "surname";
    const LAST_NAME = "lastName";
    const SITE_IDS = "siteIds";
    const QUALIFICATIONS = "qualifications";
    const AE_IDS = "aeIds";
    const ACCESS_TOKEN = "accessToken";
    const ACCOUNT_CLAIM_REQUIRED = "accountClaimRequired";
    const PASSWORD_CHANGE_REQUIRED = "passwordChangeRequired";
    const EMAIL = "email";
    const POST_CODE = "postCode";
    const POSTCODE = "postcode";
    const DATE_OF_BIRTH = "dateOfBirth";
    const PERSON_ID = "personId";
    const DRIVING_LICENCE = "drivingLicence";
    const DRIVING_LICENCE_NUMBER = "drivingLicenceNumber";
    const DRIVING_LICENCE_REGION = "drivingLicenceRegion";
    const ADDRESS_LINE_1 = "addressLine1";
    const ADDRESS_LINE_2 = "addressLine2";
    const EMAIL_ADDRESS = "emailAddress";
    const PHONE_NUMBER = "phoneNumber";
    const SECURITY_QUESTIONS_REQUIRED = "securityQuestionsRequired";
    const SECURITY_QUESTION_ONE_ID = "securityQuestionOneId";
    const SECURITY_ANSWER_ONE = "securityAnswerOne";
    const SECURITY_QUESTION_TWO_ID = "securityQuestionTwoId";
    const SECURITY_ANSWER_TWO = "securityAnswerTwo";
    const EMAIL_OPT_OUT = "emailOptOut";
    const AUTHENTICATION_METHOD = "authenticationMethod";

    public static function getDefaultParams()
    {
        $dataGeneratorHelper = DataGeneratorHelper::buildForDifferentiator([]);

        return [
            self::ADDRESS_LINE_1 => $dataGeneratorHelper->addressLine1(),
            self::USERNAME => $dataGeneratorHelper->username(),
            self::EMAIL_ADDRESS => $dataGeneratorHelper->emailAddress(),
            self::FIRST_NAME => $dataGeneratorHelper->firstName(),
            self::MIDDLE_NAME => $dataGeneratorHelper->middleName(),
            self::PHONE_NUMBER => $dataGeneratorHelper->phoneNumber(),
            self::SURNAME => $dataGeneratorHelper->surname(),
            self::POSTCODE => 'BA1 5LR',
            self::DATE_OF_BIRTH => '1980-01-01',
            self::ACCOUNT_CLAIM_REQUIRED => false,
            self::PASSWORD_CHANGE_REQUIRED => false,
            self::ADDRESS_LINE_2 => $dataGeneratorHelper->addressLine2(),
            self::SECURITY_QUESTIONS_REQUIRED => false,
            self::AUTHENTICATION_METHOD => PersonAuthType::PIN,
            self::DRIVING_LICENCE_NUMBER => $dataGeneratorHelper->drivingLicenceNumber(),
            self::DRIVING_LICENCE_REGION => LicenceCountryCode::GREAT_BRITAIN_ENGLAND_SCOTLAND_AND_WALES,
            self::QUALIFICATIONS => null
        ];
    }
}
