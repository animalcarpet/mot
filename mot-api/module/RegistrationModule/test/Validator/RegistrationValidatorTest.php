<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Validator;

use Dvsa\Mot\Api\RegistrationModule\Service\ValidatorKeyConverter;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class RegistrationValidatorTest.
 */
class RegistrationValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var RegistrationValidator */
    private $subject;

    public function setUp()
    {
        $detailsInputFilter = new DetailsInputFilter();
        $addressInputFilter = new AddressInputFilter();
        $passwordInputFilter = new PasswordInputFilter();
        $securityQuestionFirstInputFilter = new SecurityQuestionFirstInputFilter();
        $securityQuestionSecondInputFilter = new SecurityQuestionSecondInputFilter();

        $detailsInputFilter->init();
        $addressInputFilter->init();
        $passwordInputFilter->init();
        $securityQuestionFirstInputFilter->init();
        $securityQuestionSecondInputFilter->init();

        $this->subject = new RegistrationValidator(
            $detailsInputFilter,
            $addressInputFilter,
            $passwordInputFilter,
            $securityQuestionFirstInputFilter,
            $securityQuestionSecondInputFilter
        );
    }

    public function testAttach()
    {
        $testKey = 'duplicatedDetailInputFilter';
        $secondDetailInputFilter = new DetailsInputFilter();
        $secondDetailInputFilter->init();
        $this->subject->attach($secondDetailInputFilter, $testKey);

        $this->assertArrayHasKey($testKey, $this->subject->getInputFilters());

        $original = $this->subject->getInputFilters()[$testKey];
        $attached = $this->subject->getInputFilters()[DetailsInputFilter::class];

        $this->assertEquals($original, $attached);

        $this->assertNotSame($original, $attached);
    }

    public function testGetInputFilters()
    {
        $validatorInputFilters = $this->subject->getInputFilters();

        $this->assertContainsOnlyInstancesOf(InputFilterInterface::class, $validatorInputFilters);

        foreach ([
                     DetailsInputFilter::class,
                     AddressInputFilter::class,
                     PasswordInputFilter::class,
                     SecurityQuestionFirstInputFilter::class,
                     SecurityQuestionSecondInputFilter::class,
                 ] as $key) {
            $this->assertArrayHasKey($key, $validatorInputFilters);
        }
    }

    public function testValidateMissingStep()
    {
        $this->setExpectedException(\UnexpectedValueException::class);
        $this->subject->validate([]);
    }

    public function testValidate()
    {
        $this->assertInstanceOf(
            RegistrationValidator::class,
            $this->subject->validate($this->dpInvalidRegistrationData())
        );
    }

    public function testCallingIsValidTooEarly()
    {
        $this->setExpectedException(\LogicException::class);
        $this->assertFalse($this->subject->isValid());
    }

    public function testIsValid()
    {
        $this->subject->validate($this->dpInvalidRegistrationData());
        $this->assertFalse($this->subject->isValid());

        $this->subject->validate($this->dpValidRegistrationData());
        $this->assertTrue($this->subject->isValid());
    }

    public function testGetMessages()
    {
        $this->subject->validate($this->dpInvalidRegistrationData());
        $messages = $this->subject->getMessages();

        foreach ($this->subject->getInputFilters() as $key => $inputFilter) {
            $keyConvertedToStepName = ValidatorKeyConverter::inputFilterToStep($key);
            $this->assertArrayHasKey($keyConvertedToStepName, $messages);
            $this->assertEquals($messages[$keyConvertedToStepName], $inputFilter->getMessages());
        }

        $this->subject->validate($this->dpValidRegistrationData());
        $messages = $this->subject->getMessages();

        foreach ($messages as $message) {
            $this->assertEmpty($message);
        }
    }

    /**
     * @return array
     */
    public function dpValidRegistrationData()
    {
        $data = [
            DetailsInputFilter::class => [
                DetailsInputFilter::FIELD_FIRST_NAME    => 'Joe',
                DetailsInputFilter::FIELD_LAST_NAME     => 'Brown',
                DetailsInputFilter::FIELD_EMAIL         => 'joe.brown@sample.com',
                DetailsInputFilter::FIELD_EMAIL_CONFIRM => 'joe.brown@sample.com',
            ],
            AddressInputFilter::class => [
                AddressInputFilter::FIELD_ADDRESS_1    => 'Center',
                AddressInputFilter::FIELD_TOWN_OR_CITY => 'Bristol',
                AddressInputFilter::FIELD_POSTCODE     => 'BS1 1SB',
            ],
            PasswordInputFilter::class => [
                PasswordInputFilter::FIELD_PASSWORD         => 'Password1',
                PasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
            ],
            SecurityQuestionFirstInputFilter::class => [
                SecurityQuestionFirstInputFilter::FIELD_QUESTION => 1,
                SecurityQuestionFirstInputFilter::FIELD_ANSWER   => 'first question answer',
            ],
            SecurityQuestionSecondInputFilter::class => [
                SecurityQuestionSecondInputFilter::FIELD_QUESTION => 1,
                SecurityQuestionSecondInputFilter::FIELD_ANSWER   => 'second question answer',
            ],
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function dpInvalidRegistrationData()
    {
        $data = [
            DetailsInputFilter::class                => [],
            AddressInputFilter::class                => [],
            PasswordInputFilter::class               => [],
            SecurityQuestionFirstInputFilter::class  => [],
            SecurityQuestionSecondInputFilter::class => [],
        ];

        return $data;
    }
}