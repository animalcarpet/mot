<?php

namespace OrganisationTest\ViewModel\AuthorisedExaminer;

use Organisation\Form\AeCreateForm;
use Organisation\ViewModel\AuthorisedExaminer\AeFormViewModel;

class AeFormViewModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AeFormViewModel
     */
    private $model;

    public function setUp()
    {
        $this->model = new AeFormViewModel();
    }

    public function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expect
     *
     * @dataProvider dataProviderTestGetSet
     */
    public function testGetSet($property, $value, $expect = null)
    {
        $method = ucfirst($property);

        //  logical block: set value and check set method
        $result = $this->model->{'set'.$method}($value);
        $this->assertInstanceOf(AeFormViewModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['form', new AeCreateForm()],
            ['cancelUrl', 'test_CancelUrl'],
        ];
    }
}
