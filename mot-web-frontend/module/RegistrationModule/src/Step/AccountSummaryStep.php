<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;

class AccountSummaryStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = "ACCOUNT_SUMMARY";

    const QUESTIONS_GROUP_A = 'securityQuestionsGroupA';
    const QUESTIONS_GROUP_B = 'securityQuestionsGroupB';

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return $this
     */
    public function load()
    {
        return $this;
    }

    /**
     * A function that must be implemented in the step. This is how
     * we enforce that the logic of what is saved within a step, stays within that step.
     *
     * @return bool
     */
    protected function saveToSession()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [];
    }

    /**
     * Export the step values as a flattened array of all the stored session values.
     *
     * @todo Used private functions will move to a helper class.
     *
     * @return array
     */
    public function toArray()
    {
        $values = $this->sessionService->toArray();
        $result = array_reduce($values, 'array_merge', []);

        // Display name requires processing
        $result['displayName'] = $this->makeDisplayName(
            $result[DetailsInputFilter::FIELD_FIRST_NAME],
            $result[DetailsInputFilter::FIELD_MIDDLE_NAME],
            $result[DetailsInputFilter::FIELD_LAST_NAME]
        );

        // security questions
        // obscure the values but not the questions
        $result['answer1Obfuscated'] = $this->obscureValue($result[SecurityQuestionFirstInputFilter::FIELD_ANSWER]);
        $result['answer2Obfuscated'] = $this->obscureValue($result[SecurityQuestionSecondInputFilter::FIELD_ANSWER]);

        // password
        $result['passwordObfuscated'] = $this->obscureValue($result[PasswordInputFilter::FIELD_PASSWORD]);

        $result['address'] = $this->makeAddress($result);

        $result['firstQuestion'] = $this->getFirstSelectedQuestion($values);
        $result['secondQuestion'] = $this->getSecondSelectedQuestion($values);

        return $result;
    }

    /**
     * @param $values
     */
    public function getFirstSelectedQuestion($values)
    {
        $questionID = $values[SecurityQuestionOneStep::STEP_ID][SecurityQuestionFirstInputFilter::FIELD_QUESTION];
        $questionSetA = $this->sessionService->load(self::QUESTIONS_GROUP_A);

        return isset($questionSetA[$questionID]) ? $questionSetA[$questionID] : null;
    }

    public function getSecondSelectedQuestion($values)
    {
        $questionID = $values[SecurityQuestionTwoStep::STEP_ID][SecurityQuestionSecondInputFilter::FIELD_QUESTION];
        $questionSetB = $this->sessionService->load(self::QUESTIONS_GROUP_B);

        return isset($questionSetB[$questionID]) ? $questionSetB[$questionID] : null;
    }
    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'account-register/summary';
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        return;
    }

    /**
     * @todo Extract to unit tested helper service/class
     *
     * @param $firstName
     * @param $middleName
     * @param $lastName
     *
     * @return string
     */
    private function makeDisplayName($firstName, $middleName, $lastName)
    {
        return implode(' ', array_filter([$firstName, $middleName, $lastName], 'strlen'));
    }

    /**
     * @todo Extract to unit tested helper service/class
     *
     * @param $values
     *
     * @return string
     */
    private function makeAddress($values)
    {
        return array_filter(
            [
                $values['address1'],
                $values['address2'],
                $values['address3'],
                $values['townOrCity'],
                $values['postcode'],
            ],
            'strlen'
        );
    }

    /**
     * @todo Extract to unit tested helper service/class
     *
     * @param $text
     *
     * @return string
     */
    private function obscureValue($text)
    {
        return str_repeat('&bull;', strlen($text));
    }
}
