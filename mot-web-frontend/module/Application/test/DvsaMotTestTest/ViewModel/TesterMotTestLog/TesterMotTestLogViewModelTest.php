<?php

namespace OrganisationTest\ViewModel\MotTestLog;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\UrlBuilder\UrlBuilderWeb;
use DvsaMotTest\ViewModel\TesterMotTestLog\TesterMotTestLogViewModel;
use Organisation\ViewModel\MotTestLog\MotTestLogFormViewModel;
use Report\Filter\FilterBuilder;
use Report\Table\Table;

class TesterMotTestLogViewModelTest extends \PHPUnit_Framework_TestCase
{
    const ORG_ID = 9999;

    /**
     * @var TesterMotTestLogViewModel
     */
    private $model;

    /**
     * @var MotTestLogSummaryDto
     */
    private $logSummaryDto;

    /**
     * @var MotTestLogFormViewModel
     */
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

        $this->logSummaryDto = new MotTestLogSummaryDto();

        $this->model = new TesterMotTestLogViewModel(
            $this->logSummaryDto
        );

        $this->formModel = $this->model->getFormModel();
    }

    public function tearDown()
    {
        unset($this->model, $this->logSummaryDto, $this->formModel);
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
        $method = lcfirst($property);

        //  logical block: set value and check set method
        $result = $this->model->{'set'.lcfirst($property)}($value);
        $this->assertInstanceOf(TesterMotTestLogViewModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['formModel', new MotTestLogFormViewModel()],
            ['table', new Table()],
            ['filterBuilder', new FilterBuilder()],
        ];
    }

    public function testGetSetLogSummary()
    {
        //  logical block: set value and check set method
        $result = $this->model->setMotTestLogSummary($this->logSummaryDto);
        $this->assertInstanceOf(TesterMotTestLogViewModel::class, $result);

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

        $expect = UrlBuilderWeb::motTestLogDownloadCsv()->toString().
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
