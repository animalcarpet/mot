<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;

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

        // Security questions
        $result['securityQuestionOneAnswer'] = $result[SecurityQuestionsInputFilter::FIELD_ANSWER_1];
        $result['securityQuestionTwoAnswer'] = $result[SecurityQuestionsInputFilter::FIELD_ANSWER_2];

        // Password
        $result['passwordObfuscated'] = $this->obscureValue($result[PasswordInputFilter::FIELD_PASSWORD]);

        $result['address'] = $this->makeAddress($result);

        $result['firstQuestion'] = $this->getFirstSelectedQuestion($values);
        $result['secondQuestion'] = $this->getSecondSelectedQuestion($values);

        $result['dateOfBirth'] = $this->makeDateOfBirth($result);

        return $result;
    }

    /**
     * @param array $values
     *
     * @return string|null
     */
    public function getFirstSelectedQuestion($values)
    {
        $questionID = $values[SecurityQuestionsStep::STEP_ID][SecurityQuestionsInputFilter::FIELD_QUESTION_1];
        $questionSetA = $this->sessionService->load(self::QUESTIONS_GROUP_A);

        return isset($questionSetA[$questionID]) ? $questionSetA[$questionID] : null;
    }

    /**
     * @param array $values
     *
     * @return string|null
     */
    public function getSecondSelectedQuestion($values)
    {
        $questionID = $values[SecurityQuestionsStep::STEP_ID][SecurityQuestionsInputFilter::FIELD_QUESTION_2];
        $questionSetB = $this->sessionService->load(self::QUESTIONS_GROUP_B);

        return isset($questionSetB[$questionID]) ? $questionSetB[$questionID] : null;
    }
    /**
     * The route for this step.
     *
     * @return string
     */
    public function route()
    {
        return 'account-register/summary';
    }

    /**
     * @param array $values
     *
     * @return mixed|void
     */
    public function readFromArray(array $values)
    {
        return;
    }

    /**
     * @todo Extract to unit tested helper service/class
     *
     * @param string $firstName
     * @param string $middleName
     * @param string $lastName
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
     * @param array $values
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
     * @param array $values
     *
     * @return string
     */
    private function makeDateOfBirth($values)
    {
        $dateString = implode('-', array_filter([$values['year'], $values['month'], $values['day']]));

        $date = new \DateTime($dateString);
        return $date->format('j F Y');
    }

    /**
     * @todo Extract to unit tested helper service/class
     *
     * @param string $text
     *
     * @return string
     */
    private function obscureValue($text)
    {
        return str_repeat('&bull;', strlen($text));
    }
}
