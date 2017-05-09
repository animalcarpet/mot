<?php

namespace SiteTest\UpdateVtsProperty\Form;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\TestUtils\XMock;
use Site\UpdateVtsProperty\Process\Form\CountryPropertyForm;
use Core\Catalog\CountryCatalog;
use Core\Catalog\Country;
use DvsaCommon\Model\CountryOfVts;

class CountryPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CountryCatalog
     */
    private $catalog;

    protected function setUp()
    {
        $countries = [];

        foreach (CountryOfVts::getPossibleCountryCodes() as $countryCode) {
            $countries[$countryCode] = new Country($countryCode, 'countryName');
        }

        $catalog = XMock::of(CountryCatalog::class);
        $catalog
            ->expects($this->any())
            ->method('getByCode')
            ->willReturnCallback(function ($code) use ($countries) {
                return ArrayUtils::tryGet($countries, $code);
            });

        $this->catalog = $catalog;
    }

    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new CountryPropertyForm($this->catalog);
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        $data = [];
        foreach (CountryOfVts::getPossibleCountryCodes() as $countryCode) {
            $data[] = [[CountryPropertyForm::FIELD_COUNTRY => $countryCode]];
        }

        return $data;
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data)
    {
        $form = new CountryPropertyForm($this->catalog);
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $msgs = $form->getCountryElement()->getMessages();
        $this->assertCount(1, $msgs);
        $this->assertEquals(CountryPropertyForm::COUNTRY_EMPTY_MSG, array_shift($msgs));
    }

    public function invalidData()
    {
        return [
            [[CountryPropertyForm::FIELD_COUNTRY => '']],
            [[]],
        ];
    }
}
