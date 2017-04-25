<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use Core\Step\AbstractStep;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use Zend\InputFilter\InputFilter;

/**
 * Base class for RegistrationSteps.
 */
abstract class AbstractRegistrationStep extends AbstractStep
{
    /**
     * @param RegistrationSessionService $sessionService
     */
    public function __construct(RegistrationSessionService $sessionService, InputFilter $filter)
    {
        parent::__construct($sessionService, $filter);
    }

    /**
     * @return array
     */
    protected function getFieldNameMapping()
    {
        $fieldNameMapping = [
            DetailsInputFilter::FIELD_DATE => "Date of birth",
            EmailInputFilter::FIELD_EMAIL_CONFIRM => 'Re-type your email address',
            ContactDetailsInputFilter::FIELD_ADDRESS_1 => 'Address line 1',
            ContactDetailsInputFilter::FIELD_ADDRESS_2 => 'Address line 2',
            ContactDetailsInputFilter::FIELD_ADDRESS_3 => 'Address line 3',
            ContactDetailsInputFilter::FIELD_PHONE => 'Telephone number',
            SecurityQuestionsInputFilter::FIELD_QUESTION_1 => 'Select a question to answer',
            SecurityQuestionsInputFilter::FIELD_ANSWER_1 => 'First memorable answer',
            SecurityQuestionsInputFilter::FIELD_QUESTION_2 => 'Select a question to answer',
            SecurityQuestionsInputFilter::FIELD_ANSWER_2 => 'Second memorable answer',
        ];

        return $fieldNameMapping;
    }
}
