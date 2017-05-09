<?php

namespace Application\test\DvsaMotTestTest\Form\VehicleSearch;

use DvsaMotTest\Form\VehicleSearch\DuplicateCertificateVinSearchForm;

class DuplicateCertificateVinSearchFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderValidation
     */
    public function testFormValidation($data, $isValid)
    {
        $form = new DuplicateCertificateVinSearchForm();
        $form->setData($data);
        $this->assertEquals($isValid, $form->isValid());
    }

    public function dataProviderValidation()
    {
        $vin = 'vin';

        return [
            [[], false],
            [[$vin => ''], false],
            [[$vin => str_repeat('A', 21)], false],
            [[$vin => str_repeat('A', 99)], false],
            [['i-got-my-mind' => 'set_on_you'], false],
            [[$vin => '1'], true],
            [[$vin => 'a'], true],
            [[$vin => 'A'], true],
            [[$vin => '1234qwe'], true],
            [[$vin => 'żćźąś∂√źżļĶs'], true],
            [[$vin => '1M8GDM9AXKP042788'], true],
            [[$vin => 'TWENTY_characTeRs-20'], true],
        ];
    }
}
