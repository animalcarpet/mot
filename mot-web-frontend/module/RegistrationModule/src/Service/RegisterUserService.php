<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Step\ContactDetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionOneStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionTwoStep;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use DvsaCommon\UrlBuilder\RegistrationUrlBuilder;

class RegisterUserService
{
    /**
     * @todo These are shared with API so make common?
     */
    const STEP_EMAIL = 'stepEmail';
    const STEP_DETAILS = 'stepDetails';
    const STEP_CONTACT_DETAILS = 'stepContactDetails';
    const STEP_PASSWORD = 'stepPassword';
    const STEP_SECURITY_QUESTION_ONE = 'stepSecurityQuestionFirst';
    const STEP_SECURITY_QUESTION_TWO = 'stepSecurityQuestionSecond';

    const KEY_EMAIL = 'email';

    /**
     * @var HttpRestJsonClient
     */
    private $jsonClient;

    /**
     * @param HttpRestJsonClient $jsonClient
     */
    public function __construct(
        HttpRestJsonClient $jsonClient
    ) {
        $this->jsonClient = $jsonClient;
    }

    /**
     * @param array $sessionData
     *
     * @return bool
     */
    public function registerUser(array $sessionData)
    {
        $apiData = $this->prepareDataForApi($sessionData);
        $url = RegistrationUrlBuilder::register();

        try {
            $this->jsonClient->post($url, $apiData);

            return true;
        } catch (GeneralRestException $e) {
            return false;
        }
    }

    /**
     * @param array $sessionData
     *
     * @throws \Exception
     *
     * @return array
     */
    private function prepareDataForApi(array $sessionData)
    {
        $emailData = $this->dataExists($sessionData, EmailStep::STEP_ID);
        $array[self::STEP_EMAIL] = [
            EmailInputFilter::FIELD_EMAIL         => $this->dataExists($emailData, EmailInputFilter::FIELD_EMAIL),
            EmailInputFilter::FIELD_EMAIL_CONFIRM => $this->dataExists($emailData, EmailInputFilter::FIELD_EMAIL_CONFIRM),
        ];

        $detailsData = $this->dataExists($sessionData, DetailsStep::STEP_ID);
        $array[self::STEP_DETAILS] = [
            DetailsInputFilter::FIELD_FIRST_NAME    => $this->dataExists($detailsData, DetailsInputFilter::FIELD_FIRST_NAME),
            DetailsInputFilter::FIELD_MIDDLE_NAME   => $this->dataExists($detailsData, DetailsInputFilter::FIELD_MIDDLE_NAME),
            DetailsInputFilter::FIELD_LAST_NAME     => $this->dataExists($detailsData, DetailsInputFilter::FIELD_LAST_NAME),
            DetailsInputFilter::FIELD_DATE          => [
                DetailsInputFilter::FIELD_DAY           => $this->dataExists($detailsData, DetailsInputFilter::FIELD_DAY),
                DetailsInputFilter::FIELD_MONTH         => $this->dataExists($detailsData, DetailsInputFilter::FIELD_MONTH),
                DetailsInputFilter::FIELD_YEAR          => $this->dataExists($detailsData, DetailsInputFilter::FIELD_YEAR),
            ],
        ];

        $contactDetailsData = $this->dataExists($sessionData, ContactDetailsStep::STEP_ID);
        $array[self::STEP_CONTACT_DETAILS] = [
            ContactDetailsInputFilter::FIELD_ADDRESS_1    => $this->dataExists($contactDetailsData, ContactDetailsInputFilter::FIELD_ADDRESS_1),
            ContactDetailsInputFilter::FIELD_ADDRESS_2    => $this->dataExists($contactDetailsData, ContactDetailsInputFilter::FIELD_ADDRESS_2),
            ContactDetailsInputFilter::FIELD_ADDRESS_3    => $this->dataExists($contactDetailsData, ContactDetailsInputFilter::FIELD_ADDRESS_3),
            ContactDetailsInputFilter::FIELD_TOWN_OR_CITY => $this->dataExists($contactDetailsData, ContactDetailsInputFilter::FIELD_TOWN_OR_CITY),
            ContactDetailsInputFilter::FIELD_POSTCODE     => $this->dataExists($contactDetailsData, ContactDetailsInputFilter::FIELD_POSTCODE),
            ContactDetailsInputFilter::FIELD_PHONE        => $this->dataExists($contactDetailsData, ContactDetailsInputFilter::FIELD_PHONE),
        ];

        $passwordData = $this->dataExists($sessionData, PasswordStep::STEP_ID);
        $array[self::STEP_PASSWORD] = [
            PasswordInputFilter::FIELD_PASSWORD         => $this->dataExists($passwordData, PasswordInputFilter::FIELD_PASSWORD),
            PasswordInputFilter::FIELD_PASSWORD_CONFIRM => $this->dataExists($passwordData, PasswordInputFilter::FIELD_PASSWORD_CONFIRM),
        ];

        $security1Data = $this->dataExists($sessionData, SecurityQuestionOneStep::STEP_ID);
        $array[self::STEP_SECURITY_QUESTION_ONE] = [
            SecurityQuestionFirstInputFilter::FIELD_QUESTION => $this->dataExists($security1Data, SecurityQuestionFirstInputFilter::FIELD_QUESTION),
            SecurityQuestionFirstInputFilter::FIELD_ANSWER   => $this->dataExists($security1Data, SecurityQuestionFirstInputFilter::FIELD_ANSWER),
        ];

        $security2Data = $this->dataExists($sessionData, SecurityQuestionTwoStep::STEP_ID);
        $array[self::STEP_SECURITY_QUESTION_TWO] = [
            SecurityQuestionSecondInputFilter::FIELD_QUESTION => $this->dataExists($security2Data, SecurityQuestionSecondInputFilter::FIELD_QUESTION),
            SecurityQuestionSecondInputFilter::FIELD_ANSWER   => $this->dataExists($security2Data, SecurityQuestionSecondInputFilter::FIELD_ANSWER),
        ];

        return $array;
    }

    /**
     * @param array  $data
     * @param string $dataKey
     *
     * @throws \Exception if data does not exist
     *
     * @return array
     */
    private function dataExists(array $data, $dataKey)
    {
        if (!isset($data[$dataKey])) {
            throw new \Exception("Data key [{$dataKey}] not set");
        }

        return $data[$dataKey];
    }
}
