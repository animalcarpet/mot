<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Form\SecurityCardAddressForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Step\AddressStep;

class SecurityCardAddressFormTest extends \PHPUnit_Framework_TestCase
{
    const INVALID_HOME_POSTCODE = 'HT1 0EW';
    const VALID_HOME_POSTCODE = 'BT9 6FT';

    /**
     * @dataProvider dataProviderCustom_Valid
     */
    public function test_validCustomAddress_shouldNotProduceErrorMessage($addressData)
    {
        $form = new SecurityCardAddressForm([]);
        $form->setData($addressData);
        $this->assertTrue($form->isValid());
        $this->assertEmpty($form->getMessages());
    }

    public function test_validAddressChoice_shouldNotProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm($this->getAddressValues(self::VALID_HOME_POSTCODE));

        $addressData = [
            'address1' => 'Home',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => 'NG1 6LP',
            'addressChoice' => '1'
        ];

        $form->setData($addressData);
        $this->assertTrue($form->isValid());
        $this->assertEmpty($form->getMessages());
    }

    public function test_invalidPostcodeForHomeAddress_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm($this->getAddressValues(self::INVALID_HOME_POSTCODE));

        $addressData = [
            'address1' => 'Home',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => self::INVALID_HOME_POSTCODE,
            'addressChoice' => '0'
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
        $this->assertEquals(
            SecurityCardAddressForm::MSG_INVALID_HOME_POSTCODE, $form->getMessages()['addressChoice'][0]);
    }

    public function test_validPostcodeForHomeAddress_shouldNotProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm($this->getAddressValues(self::VALID_HOME_POSTCODE));

        $addressData = [
            'address1' => 'Home',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => self::VALID_HOME_POSTCODE,
            'addressChoice' => '0'
        ];

        $form->setData($addressData);
        $this->assertTrue($form->isValid());
    }

    public function test_emptyAddressChoice_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Home',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => 'NG1 6LP',
            'addressChoice' => ''
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_emptyAddressOne_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => '',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => 'NG1 6LP',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_emptyTown_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Address 1',
            'address2' => '',
            'address3' => '',
            'townOrCity' => '',
            'postcode' => 'NG1 6LP',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }


    public function test_emptyPostcode_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Address 1',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => '',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_InvalidPostcode_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Address 1',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => 'ehjgefieij',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_Address1ExceedsMaxLength_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => 'NG1 1PL',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_Address2ExceedsMaxLength_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Line 1',
            'address2' => 'Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1',
            'address3' => '',
            'townOrCity' => 'Town',
            'postcode' => 'NG1 1PL',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_Address3ExceedsMaxLength_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Line 1',
            'address2' => '',
            'address3' => 'Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1',
            'townOrCity' => 'Town',
            'postcode' => 'NG1 1PL',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function test_TownExceedsMaxLength_shouldProduceErrorMessage()
    {
        $form = new SecurityCardAddressForm([]);

        $addressData = [
            'address1' => 'Line 1',
            'address2' => '',
            'address3' => '',
            'townOrCity' => 'Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1Address 1',
            'postcode' => 'NG1 1PL',
            'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
        ];

        $form->setData($addressData);
        $this->assertFalse($form->isValid());
    }

    public function testRadioButtonValuesArePopulatedCorrectlyWithAddressData()
    {
        $form = new SecurityCardAddressForm($this->getAddressValues(self::VALID_HOME_POSTCODE));

        $addressRadioValues = $form->getAddressRadios()->getValueOptions();

        $this->assertCount(4, $addressRadioValues);
    }

    public function testRadioButtonsArePopulatedCorrectlyWithNoAddressData()
    {
        $form = new SecurityCardAddressForm([]);

        $addressRadioValues = $form->getAddressRadios()->getValueOptions();

        $this->assertCount(1, $addressRadioValues);
    }

    public static function dataProviderCustom_Valid()
    {
        return [
            [
                [
                    'address1' => '73 southwell avenue',
                    'address2' => 'address 2',
                    'address3' => 'address 3',
                    'townOrCity' => 'Northolt',
                    'postcode' => 'NG1 6LP',
                    'addressChoice' => SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE
                ]
            ],
        ];
    }

    private function getAddressValues($homeAddressPostcode)
    {
        return [
            [
                'name' => "Home",
                'addressLine1' => "Home Address Line 1",
                'addressLine2' => "Home Address Line 2",
                'addressLine3' => "Home Address Line 3",
                'town' => "Home Town",
                'postcode' => $homeAddressPostcode,
                'addressString' => 'Home Address Line 1, Home Address Line 2, Home Address Line 3, Home Town, BT9 6FT',
            ],
            [
                'name' => "Popular Garages",
                'addressString' => '67 Main Road, Bristol, BS8 2NT',
                "addressLine1" => "67 Main Road",
                "addressLine2" => null,
                "addressLine3" => null,
                "town" => "Bristol",
                "postcode" => "BS8 2NT",
            ],
            [
                'name' => "Test VTS",
                'addressString' => '68 Main Road, Bristol, BS8 2NT',
                "addressLine1" => "68 Main Road",
                "addressLine2" => null,
                "addressLine3" => null,
                "town" => "Bristol",
                "postcode" => "BS8 2NT",
            ],
        ];
    }
}
