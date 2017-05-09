<?php

namespace DvsaClientTest\ViewModel;

use DvsaClient\ViewModel\DateTimeViewModel;

class DateTimeViewModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeViewModel
     */
    private $model;

    public function setUp()
    {
        $this->model = new DateTimeViewModel(1987);
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
        $this->assertInstanceOf(DateTimeViewModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'day',
                'value' => 1,
            ],
            ['month', '2'],
            ['year', '2015'],
            ['hour', 23],
            ['minute', 59],
            ['second', 58],
            ['date', new \DateTime()],
        ];
    }

    public function testDefault()
    {
        $this->assertEquals(new \DateTime('1987-01-01 00:00:00'), $this->model->getDate());
    }

    /**
     * @dataProvider dataProviderTestGetDateReturnNullIfPartIsInvalid
     */
    public function testGetDateReturnNullIfPartIsInvalid($datePart, $value)
    {
        $this->model->{'set'.lcfirst($datePart)}($value);
        $actual = $this->model->getDate();
        print_r($actual);
        $this->assertEquals(null, $actual);
    }

    public function dataProviderTestGetDateReturnNullIfPartIsInvalid()
    {
        return [
            [
                'datePart' => 'year',
                'value' => 'a',
            ],
            ['month', 'invalid'],
            ['month', 13],
            ['month', 0],
            ['day', 'invalid'],
            ['day', 32],
            ['day', 0],
            ['hour', 'invalid'],
            ['hour', 25],
            ['minute', 'invalid'],
            ['minute', 60],
            ['second', 'invalid'],
            ['second', 60],
        ];
    }
}
