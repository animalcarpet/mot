<?php

namespace OrganisationTest\ViewModel\MotTestLog;

use DvsaClient\ViewModel\DateTimeViewModel;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Messages\DateErrors;
use Organisation\ViewModel\MotTestLog\MotTestLogFormViewModel;
use Zend\Stdlib\Parameters;
use Zend\Validator\Date;

class MotTestLogFormViewModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotTestLogFormViewModel */
    private $model;

    public function setUp()
    {
        $this->model = new MotTestLogFormViewModel();
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
        $this->assertInstanceOf(MotTestLogFormViewModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'dateFrom',
                'value' => new DateTimeViewModel(2013, 12, 11),
            ],
            [
                'property' => 'dateTo',
                'value' => (new DateTimeViewModel())->setDate(new \DateTime('2012-11-10')),
                'expect' => new DateTimeViewModel(2012, 11, 10),
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestParseData
     */
    public function testParseData($queryParams, $expect)
    {
        $this->model->parseData(new Parameters($queryParams));

        //  logig block: check
        $this->assertEquals($expect['dateFrom'], $this->model->getDateFrom());
        $this->assertEquals($expect['dateTo'], $this->model->getDateTo());
    }

    public function dataProviderTestParseData()
    {
        return [
            //  logical block: check dates provided by form
            //  both dates are not set
            [
                'queryParams' => [
                ],
                'expect' => [
                    'dateFrom' => new DateTimeViewModel(),
                    'dateTo' => new DateTimeViewModel(),
                ],
            ],
            //  both dates are set
            [
                'queryParams' => [
                    'dateFrom' => [
                        'Year' => '2010',
                        'Month' => '09',
                        'Day' => '08',
                    ],
                    'dateTo' => [
                        'Year' => '2001',
                        'Month' => '02',
                        'Day' => '03',
                    ],
                ],
                'expect' => [
                    'dateFrom' => new DateTimeViewModel(2010, 9, 8),
                    'dateTo' => new DateTimeViewModel(2001, 02, 03, 23, 59, 59),
                ],
            ],

            //  logical block: check date provided as query parameters
            //  both dates are not set
            [
                'queryParams' => [
                ],
                'expect' => [
                    'dateFrom' => new DateTimeViewModel(),
                    'dateTo' => new DateTimeViewModel(),
                ],
            ],
            //  both dates are set
            [
                'queryParams' => [
                    SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => (new \DateTime('2005-06-07'))->getTimestamp(),
                    SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM => (new \DateTime('1990-01-02'))->getTimestamp(),
                ],
                'expect' => [
                    'dateFrom' => new DateTimeViewModel(2005, 6, 7),
                    'dateTo' => new DateTimeViewModel(1990, 1, 2),
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestValidation
     */
    public function testValidation($dateFrom, $dateTo, $expect)
    {
        $this->model
            ->setDateFrom($dateFrom)
            ->setDateTo($dateTo);

        $actual = $this->model->isValid();

        //  logig block: check
        $this->assertEquals($expect['isValid'], $actual);

        if (!empty($expect['errors'])) {
            foreach ($expect['errors'] as $field => $error) {
                $this->assertEquals($error, $this->model->getError($field));
            }
        }
    }

    public function dataProviderTestValidation()
    {
        return [
            //  from date is invalid, to date in future
            [
                'dateFrom' => new DateTimeViewModel(2013, 12, 'a'),
                'dateTo' => new DateTimeViewModel(date('Y') + 1, 01, 02),
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        MotTestLogFormViewModel::FLD_DATE_FROM => DateErrors::NOT_EXIST,
                        MotTestLogFormViewModel::FLD_DATE_TO => DateErrors::IN_FUTURE,
                    ],
                ],
            ],
            //  from date in future, to date is invalid 30 February
            [
                'dateFrom' => new DateTimeViewModel(date('Y') + 1, 10, 9),
                'dateTo' => new DateTimeViewModel(1900, 2, 30),
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        MotTestLogFormViewModel::FLD_DATE_FROM => DateErrors::IN_FUTURE,
                        MotTestLogFormViewModel::FLD_DATE_TO => DateErrors::NOT_EXIST,
                    ],
                ],
            ],
            //  date from after date to
            [
                'dateFrom' => new DateTimeViewModel(2015, 5, 10),
                'dateTo' => new DateTimeViewModel(2015, 5, 9),
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        MotTestLogFormViewModel::FLD_DATE_FROM => DateErrors::AFTER_TO,
                    ],
                ],
            ],
            //  date interval more than 31 day
            [
                'dateFrom' => new DateTimeViewModel(2014, 3, 2),
                'dateTo' => new DateTimeViewModel(2015, 4, 3),
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        MotTestLogFormViewModel::FLD_DATE_FROM => DateErrors::RANGE_31D,
                    ],
                ],
            ],
            //  date before 1900
            [
                'dateFrom' => new DateTimeViewModel(1610, 7, 4), // interesting date ...
                'dateTo' => new DateTimeViewModel(1985, 7, 7),
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        MotTestLogFormViewModel::FLD_DATE_FROM => DateErrors::NOT_EXIST,
                    ],
                ],
            ],
            //  date before 1900
            [
                'dateFrom' => new DateTimeViewModel('', 7, 4), // interesting date ...
                'dateTo' => new DateTimeViewModel(2014, '', 7),
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        MotTestLogFormViewModel::FLD_DATE_FROM => DateErrors::INVALID_FORMAT,
                        MotTestLogFormViewModel::FLD_DATE_TO => DateErrors::INVALID_FORMAT,
                    ],
                ],
            ],
            //  valid
            [
                'dateFrom' => new DateTimeViewModel(2015, 3, 2),
                'dateTo' => new DateTimeViewModel(2015, 3, 4),
                'expect' => [
                    'isValid' => true,
                ],
            ],

        ];
    }
}
