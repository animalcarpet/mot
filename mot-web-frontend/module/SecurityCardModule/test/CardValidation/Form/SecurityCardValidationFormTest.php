<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Form\SecurityCardValidationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidationCallback;
use DvsaCommonTest\TestUtils\XMock;

class SecurityCardValidationFormTest extends \PHPUnit_Framework_TestCase
{
    public static function dataProvider_invalidPin()
    {
        return [
            ['', 'Enter a PIN number', 'onBlankPin'],
            ['ABCDEDF', 'Enter a valid PIN number', 'onNonNumeric'],
            ['1234', 'Enter a 6 digit number', 'onInvalidLength'],
            ['1234567', 'Enter a 6 digit number', 'onInvalidLength'],
        ];
    }

    /**
     * @dataProvider dataProvider_invalidPin
     */
    public function test_invalidPin_shouldProduceErrorMessage($invalidPin, $errorMessage)
    {
        $form = new SecurityCardValidationForm();
        $data = ['pin' => $invalidPin];

        $form->setData($data);
        $form->isValid();

        $messages = array_values($form->getMessages('pin'));

        $this->assertEquals($errorMessage, reset($messages));
        $this->assertCount(1, $messages);
    }

    /**
     * @dataProvider dataProvider_invalidPin
     */
    public function test_invalidPin_shouldInvokeCallback($invalidPin, $ignore, $callbackMethod)
    {
        $pinValidationCallback = XMock::of(SecurityCardPinValidationCallback::class);
        $pinValidationCallback->expects($this->once())
            ->method($callbackMethod);

        $form = new SecurityCardValidationForm($pinValidationCallback);
        $data = ['pin' => $invalidPin];

        $form->setData($data);
        $form->isValid();
    }
}
