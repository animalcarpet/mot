<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest\Service;

use Dvsa\Mot\Api\RegistrationModule\Service\ValidatorKeyConverter;
use DvsaCommon\Validator\EmailAddressValidator;

/**
 * Class ValidatorKeyConverterTest.
 */
class ValidatorKeyConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertingStepNameToInputFilterName()
    {
        $this->assertEquals(
            $this->dpDataWithInputFilterKeys(),
            ValidatorKeyConverter::stepsToInputFilters($this->dpDataWithStepKeys())
        );
    }

    public function testConvertingInputFilterNameToStepName()
    {
        $this->assertEquals(
            $this->dpDataWithStepKeys(),
            ValidatorKeyConverter::inputFiltersToSteps($this->dpDataWithInputFilterKeys())
        );
    }

    private function dpDataWithInputFilterKeys()
    {
        return [
            'DvsaCommon\InputFilter\Registration\DetailsInputFilter' => [
                'firstName' => 'Joe',
                'middleName' => 'Light',
                'lastName' => 'Brown',
                'emailAddress' => 'validatorkeyconvertertest@'.EmailAddressValidator::TEST_DOMAIN,
                'reTypeYourEmailAddress' => 'validatorkeyconvertertest@'.EmailAddressValidator::TEST_DOMAIN,
            ],
            'DvsaCommon\InputFilter\Registration\AddressInputFilter' => [
                'addressLine1' => 'Center',
                'townOrCity' => 'Bristol',
                'postCode' => 'BS1 1SB',
            ],
            'DvsaCommon\InputFilter\Registration\PasswordInputFilter' => [
                'createAPassword' => 'Password1',
                'reTypeYourPassword' => 'Password1',
            ],
            'DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter' => [
                'selectAQuestionToAnswerFirst' => 1,
                'yourAnswerFirst' => 'first question answer',
            ],
            'DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter' => [
                'selectAQuestionToAnswerSecond' => 2,
                'yourAnswerSecond' => 'second question answer',
            ],
        ];
    }

    private function dpDataWithStepKeys()
    {
        return [
            'stepDetails' => [
                'firstName' => 'Joe',
                'middleName' => 'Light',
                'lastName' => 'Brown',
                'emailAddress' => 'validatorkeyconvertertest@'.EmailAddressValidator::TEST_DOMAIN,
                'reTypeYourEmailAddress' => 'validatorkeyconvertertest@'.EmailAddressValidator::TEST_DOMAIN,
            ],
            'stepAddress' => [
                'addressLine1' => 'Center',
                'townOrCity' => 'Bristol',
                'postCode' => 'BS1 1SB',
            ],
            'stepPassword' => [
                'createAPassword' => 'Password1',
                'reTypeYourPassword' => 'Password1',
            ],
            'stepSecurityQuestionFirst' => [
                'selectAQuestionToAnswerFirst' => 1,
                'yourAnswerFirst' => 'first question answer',
            ],
            'stepSecurityQuestionSecond' => [
                'selectAQuestionToAnswerSecond' => 2,
                'yourAnswerSecond' => 'second question answer',
            ],
        ];
    }
}
