<?php

namespace OrganisationTest\UpdateAeProperty\Form;

use Organisation\UpdateAeProperty\Process\Form\TradingNamePropertyForm;

class TradingNamePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new TradingNamePropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[TradingNamePropertyForm::FIELD_NAME => 'AE trading name']],
            [[TradingNamePropertyForm::FIELD_NAME => 'AE trading name 2 !']],
            [[TradingNamePropertyForm::FIELD_NAME => $this->createName(TradingNamePropertyForm::FIELD_NAME_MAX_LENGTH)]],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data, $expectedMsg)
    {
        $form = new TradingNamePropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $messages = $form->getTradingNameElement()->getMessages();
        $this->assertCount(1, $messages);
        $this->assertEquals($expectedMsg, array_shift($messages));
    }

    public function invalidData()
    {
        return [
            [[TradingNamePropertyForm::FIELD_NAME => ''], TradingNamePropertyForm::TRADING_NAME_EMPTY_MSG],
            [[TradingNamePropertyForm::FIELD_NAME => ' '], TradingNamePropertyForm::TRADING_NAME_EMPTY_MSG],
            [[TradingNamePropertyForm::FIELD_NAME => $this->createName(TradingNamePropertyForm::FIELD_NAME_MAX_LENGTH, ' ')],
                TradingNamePropertyForm::TRADING_NAME_EMPTY_MSG, ],
            [[TradingNamePropertyForm::FIELD_NAME => $this->createName(TradingNamePropertyForm::FIELD_NAME_MAX_LENGTH + 1)],
                str_replace('%max%', TradingNamePropertyForm::FIELD_NAME_MAX_LENGTH, TradingNamePropertyForm::TRADING_NAME_TOO_LONG_MSG), ],
        ];
    }

    private function createName($length, $char = 'X')
    {
        $name = '';
        while ($length) {
            $name .= $char;
            --$length;
        }

        return $name;
    }
}
