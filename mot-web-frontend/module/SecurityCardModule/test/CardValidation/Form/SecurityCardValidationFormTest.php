<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Form\SecurityCardValidationForm;

class SecurityCardValidationFormTest extends \PHPUnit_Framework_TestCase
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
        $form = new SecurityCardValidationForm();
        $data = ['pin' => $invalidPin];

        $form->setData($data);
        $form->isValid();

        $messages = array_values($form->getMessages('pin'));

        $this->assertEquals($errorMessage, reset($messages));
        $this->assertCount(1, $messages);
    }

}