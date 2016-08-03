<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Step\AddressStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionOneStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionTwoStep;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use DvsaCommon\UrlBuilder\RegistrationUrlBuilder;

class RegisterUserService
{
    /**
     * @todo These are shared with API so make common?
     */
    const STEP_DETAILS = 'stepDetails';
    const STEP_ADDRESS = 'stepAddress';
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
     * @param $emailAddress
     * @return bool
     */
    public function isEmailDuplicated($emailAddress)
    {
        $url = RegistrationUrlBuilder::checkEmail();
        $payload = [self::KEY_EMAIL => $emailAddress];

        $response = $this->jsonClient->post($url, $payload);

        if (
            !is_array($response) ||
            !array_key_exists('data', $response) ||
            !array_key_exists('isExists',  $response['data'])
        ){
            throw new \LogicException('Expected to receive a valid response from the API containing nesting "date" and "isExists" keys');
        }

        return $response['data']['isExists'];
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
        $detailsData = $this->dataExists($sessionData, DetailsStep::STEP_ID);
        $array[self::STEP_DETAILS] = [
            DetailsInputFilter::FIELD_FIRST_NAME    => $this->dataExists($detailsData, DetailsInputFilter::FIELD_FIRST_NAME),
            DetailsInputFilter::FIELD_MIDDLE_NAME   => $this->dataExists($detailsData, DetailsInputFilter::FIELD_MIDDLE_NAME),
            DetailsInputFilter::FIELD_LAST_NAME     => $this->dataExists($detailsData, DetailsInputFilter::FIELD_LAST_NAME),
            DetailsInputFilter::FIELD_PHONE         => $this->dataExists($detailsData, DetailsInputFilter::FIELD_PHONE),
            DetailsInputFilter::FIELD_EMAIL         => $this->dataExists($detailsData, DetailsInputFilter::FIELD_EMAIL),
            DetailsInputFilter::FIELD_EMAIL_CONFIRM => $this->dataExists($detailsData, DetailsInputFilter::FIELD_EMAIL_CONFIRM),
        ];

        $addressData = $this->dataExists($sessionData, AddressStep::STEP_ID);
        $array[self::STEP_ADDRESS] = [
            AddressInputFilter::FIELD_ADDRESS_1    => $this->dataExists($addressData, AddressInputFilter::FIELD_ADDRESS_1),
            AddressInputFilter::FIELD_ADDRESS_2    => $this->dataExists($addressData, AddressInputFilter::FIELD_ADDRESS_2),
            AddressInputFilter::FIELD_ADDRESS_3    => $this->dataExists($addressData, AddressInputFilter::FIELD_ADDRESS_3),
            AddressInputFilter::FIELD_TOWN_OR_CITY => $this->dataExists($addressData, AddressInputFilter::FIELD_TOWN_OR_CITY),
            AddressInputFilter::FIELD_POSTCODE     => $this->dataExists($addressData, AddressInputFilter::FIELD_POSTCODE),
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
