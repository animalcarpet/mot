<?php

namespace OrganisationTest\ViewModel\MotTestLog;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use Organisation\ViewModel\MotTestLog\MotTestLogFormViewModel;
use Organisation\ViewModel\MotTestLog\MotTestLogViewModel;
use Report\Filter\FilterBuilder;
use Report\Table\Table;

class MotTestLogViewModelTest extends \PHPUnit_Framework_TestCase
{
    const ORG_ID = 9999;

    /** @var MotTestLogViewModel */
    private $model;
    /** @var OrganisationDto */
    private $orgDto;
    /** @var MotTestLogSummaryDto */
    private $logSummaryDto;
    /** @var MotTestLogFormViewModel */
    private $formModel;

    /**
     * @var \DateTime
     */
    private $mondayLastWeek;
    /**
     * @var \DateTime
     */
    private $sundayLastWeek;

    public function setUp()
    {
        $this->sundayLastWeek = new \DateTime('@'.strtotime('sunday last week 23:59:59'));
        $this->mondayLastWeek = new \DateTime('@'.strtotime('monday last week 00:00:00'));

        //  logic block :: prepare instance of tested class
        $this->orgDto = (new OrganisationDto())->setId(self::ORG_ID);
        $this->logSummaryDto = new MotTestLogSummaryDto();

        $this->model = new MotTestLogViewModel(
            $this->orgDto,
            $this->logSummaryDto
        );

        $this->formModel = $this->model->getFormModel();
    }

    public function tearDown()
    {
        unset($this->model, $this->orgDto, $this->logSummaryDto, $this->formModel);
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
        $this->assertInstanceOf(MotTestLogViewModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'organisation',
                'value' => new OrganisationDto(),
            ],
            ['formModel', new MotTestLogFormViewModel()],
            ['table', new Table()],
            ['filterBuilder', new FilterBuilder()],
        ];
    }

    public function testGetSetLogSummary()
    {
        //  logical block: set value and check set method
        $result = $this->model->setMotTestLogSummary($this->logSummaryDto);
        $this->assertInstanceOf(MotTestLogViewModel::class, $result);

        //  logical block: check get method
        $this->assertEquals($this->logSummaryDto, $this->model->getMotTestLogSummary());
    }

    public function testDefaultValues()
    {
        $this->assertEquals($this->sundayLastWeek, $this->formModel->getDateTo()->getDate());
        $this->assertEquals($this->mondayLastWeek, $this->formModel->getDateFrom()->getDate());
    }

    public function testGetDownloadUrl()
    {
        $dateFromTs = $this->mondayLastWeek->getTimestamp();
        $dateToTs = $this->sundayLastWeek->getTimestamp();

        $expect = AuthorisedExaminerUrlBuilderWeb::motTestLogDownloadCsv(self::ORG_ID)->toString().
            '?'.
            http_build_query(
                [
                    SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => $dateFromTs,
                    SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM => $dateToTs,
                ]
            );

        $this->assertEquals($expect, $this->model->getDownloadUrl());
    }
}
