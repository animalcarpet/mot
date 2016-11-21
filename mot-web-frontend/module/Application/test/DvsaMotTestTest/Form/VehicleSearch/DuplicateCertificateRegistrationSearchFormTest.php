<?php

namespace Application\test\DvsaMotTestTest\Form\VehicleSearch;

use DvsaMotTest\Form\VehicleSearch\DuplicateCertificateRegistrationSearchForm;

class DuplicateCertificateRegistrationSearchFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderValidation
     */
    public function testFormValidation($data, $isValid)
    {
        $form = new DuplicateCertificateRegistrationSearchForm();
        $form->setData($data);
        $this->assertEquals($isValid, $form->isValid());
    }

    public function dataProviderValidation()
    {
        $registration = 'vrm';

        return [
            [[], false],
            [[$registration => ''], false],
            [[$registration => str_repeat('A', 14)], false],
            [[$registration => str_repeat('A', 99)], false],
            [['take-on-me' => 'take-me-on'], false],
            [[$registration => '1'], true],
            [[$registration => 'a'], true],
            [[$registration => 'A'], true],
            [[$registration => '1234qwe'], true],
            [[$registration => 'żćźąś∂√źżļĶs'], true],
            [[$registration => 'MOT-12202'], true],
            [[$registration => 'FNZ6110'], true],
            [[$registration => 'UNDERSCORES__'], true],
        ];
    }
}