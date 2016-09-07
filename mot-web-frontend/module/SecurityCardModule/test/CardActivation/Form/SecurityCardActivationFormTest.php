<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form\SecurityCardActivationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidationCallback;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardSerialNumberValidationCallback;
use DvsaCommonTest\TestUtils\XMock;

class SecurityCardActivationFormTest extends \PHPUnit_Framework_TestCase
{

    public static function dataProvider_invalidPin()
    {
        return [
            ['','Enter a PIN number'],
            ['ABCDEDF', 'Enter a valid PIN number'],
            ['1234', 'Enter a 6 digit number'],
            ['1234567', 'Enter a 6 digit number']
        ];
    }

    /**
     * @dataProvider dataProvider_invalidPin
     */
    public function test_invalidPin_shouldProduceErrorMessage($invalidPin, $errorMessage)
    {
        $form = new SecurityCardActivationForm();
        $data = ['serial_number' => 'STTA12345678', 'pin' => $invalidPin];

        $form->setData($data);
        $form->isValid();

        $messages = array_values($form->getMessages('pin'));

        $this->assertEquals($errorMessage, reset($messages));
        $this->assertCount(1, $messages);
    }

    public static function dataProvider_invalidSerialNumber()
    {

        return [[''],['STTA1234567'],['STTAABCDEFG']];
    }

    /**
     * @dataProvider dataProvider_invalidSerialNumber
     */
    public function test_whenSerialNumberIsInvalid_shouldProduceErrorMessage($invalidSerialNumber)
    {
        $form = new SecurityCardActivationForm();
        $data = ['serial_number' => $invalidSerialNumber, 'pin' => '123456'];

        $form->setData($data);
        $form->isValid();

        $messages = array_values($form->getMessages('serial_number'));

        $this->assertEquals('Enter a valid serial number', reset($messages));
        $this->assertCount(1, $messages);
    }

    /**
     * @dataProvider dataProvider_invalidSerialNumber
     */
    public function test_invalidSerialNumber_shouldInvokeCallback($invalidSerialNumber)
    {
        $pinValidationCallback = XMock::of(SecurityCardPinValidationCallback::class);
        $serialNumberValidationCallback = XMock::of(SecurityCardSerialNumberValidationCallback::class);
        $serialNumberValidationCallback->expects($this->once())
            ->method('onInvalidFormat');

        $form = new SecurityCardActivationForm($pinValidationCallback, $serialNumberValidationCallback);
        $data = ['serial_number' => $invalidSerialNumber, 'pin' => '123456'];

        $form->setData($data);
        $form->isValid();
    }

    public function test_givenCorrectBindData_formShouldBeValid()
    {
        $form = new SecurityCardActivationForm();
        $data = ['serial_number' => 'STTA12345678', 'pin' => '123456'];

        $form->setData($data);
        $this->assertTrue($form->isValid());
    }

    public function test_givenSerialNumberAndPinAreIncorrect_shouldHave2Messages()
    {
        $form = new SecurityCardActivationForm();
        $data = ['serial_number' => 'STTA123', 'pin' => '1456'];

        $form->setData($data);
        $form->isValid();

        $this->assertArrayHasKey('serial_number', $form->getMessages());
        $this->assertArrayHasKey('pin', $form->getMessages());
    }
}
