<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Validator;

use Dvsa\Mot\Api\RegistrationModule\Service\ValidatorKeyConverter;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use Zend\InputFilter\InputFilter;

/**
 * Class RegistrationValidator.
 */
class RegistrationValidator
{
    const EXP_CALLING_IS_VALID_BEFORE_VALIDATING = '%s::%s method has to be called before calling %s::%s';
    const EXP_MISSING_STEP_DATA = 'Required data for "%s" step is not provided';

    /** @var bool */
    private $isValid;

    /** @var InputFilter[] */
    private $inputFilters;

    /**
     * RegistrationValidator constructor.
     *
     * @param EmailInputFilter             $emailInputFilter
     * @param DetailsInputFilter           $detailsInputFilter
     * @param ContactDetailsInputFilter    $contactDetailsInputFilter
     * @param PasswordInputFilter          $passwordInputFilter
     * @param SecurityQuestionsInputFilter $securityQuestionsInputFilter
     */
    public function __construct(
        EmailInputFilter $emailInputFilter,
        DetailsInputFilter $detailsInputFilter,
        ContactDetailsInputFilter $contactDetailsInputFilter,
        PasswordInputFilter $passwordInputFilter,
        SecurityQuestionsInputFilter $securityQuestionsInputFilter
    ) {
        $this->attach($emailInputFilter);
        $this->attach($detailsInputFilter);
        $this->attach($contactDetailsInputFilter);
        $this->attach($passwordInputFilter);
        $this->attach($securityQuestionsInputFilter);
    }

    /**
     * Attache an inputFilter to the validator.
     *
     * @param InputFilter $inputFilter
     * @param string|null $dataSetKey  (optional) If its null the give inputFilter's class name will be used instead
     */
    public function attach(InputFilter $inputFilter, $dataSetKey = null)
    {
        if (is_null($dataSetKey)) {
            $dataSetKey = get_class($inputFilter);
        }

        $this->inputFilters[$dataSetKey] = $inputFilter;
    }

    public function getInputFilters()
    {
        return $this->inputFilters;
    }

    /**
     * Validates the given nested array of all steps and their respective fields (key/value pairs)
     * Note! step names (main keys) should be matching with the attached inputFilters keys.
     *
     * @param $data
     *
     * @return RegistrationValidator
     */
    public function validate($data)
    {
        $this->isValid = true;

        $data = ValidatorKeyConverter::stepsToInputFilters($data);

        foreach ($this->getInputFilters() as $key => $inputFilter) {
            if (!isset($data[$key])) {
                throw new \UnexpectedValueException(
                    sprintf(self::EXP_MISSING_STEP_DATA, ValidatorKeyConverter::inputFilterToStep($key))
                );
            }

            $inputFilter->setData($data[$key]);
            $isValid = $inputFilter->isValid();

            if (!$isValid) {
                $this->isValid = false;
            }
        }

        return $this;
    }

    /**
     * Indicate if all the inputFilters been validated successfully or not.
     *
     * @return bool
     */
    public function isValid()
    {
        if (is_null($this->isValid)) {
            throw new \LogicException(
                sprintf(
                    '%s::%s method has to be called before calling %s::%s',
                    self::class,
                    'validate',
                    self::class,
                    __METHOD__
                )
            );
        }

        return $this->isValid;
    }

    /**
     * Return all the potential messages form the attached input filters.
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = [];

        foreach ($this->getInputFilters() as $key => $inputFilter) {
            if (!empty($inputFilter->getMessages())) {
                $messages[$key] = $inputFilter->getMessages();
            }
        }

        return ValidatorKeyConverter::inputFiltersToSteps($messages);
    }
}
