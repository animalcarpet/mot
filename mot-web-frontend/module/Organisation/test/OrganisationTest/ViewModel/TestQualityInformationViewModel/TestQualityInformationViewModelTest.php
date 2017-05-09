<?php

namespace OrganisationTest\ViewModel\TestQualityInformationViewModel;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use Organisation\Action\TestQualityInformationAction;
use Organisation\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use Site\Service\RiskAssessmentScoreRagClassifier;

class TestQualityInformationViewModelTest extends AbstractFrontendControllerTestCase
{
    const PAGE_NUMBER = 1;
    const RETURN_URL = 'http://www.url.pl';

    private $config;

    /** @var RiskAssessmentScoreRagClassifier */
    private $riskAssessmentScoreRagClassifier;

    /** @var TestQualityInformationViewModel */
    private $testQualityInformationViewModel;

    protected function setUp()
    {
        $this->mockConfig();

        $this->riskAssessmentScoreRagClassifier = new RiskAssessmentScoreRagClassifier(0, $this->config);
    }

    /** @dataProvider dataProviderTableValues
     * @param $rowsCount
     * @param $rowsTotalCount
     * @param $pageNr
     */
    public function testCreateTable($rowsCount, $rowsTotalCount, $pageNr)
    {
        $this->testQualityInformationViewModel = new TestQualityInformationViewModel(
            $this->buildAuthorisedExaminerSitePerformanceDto($rowsTotalCount),
            self::RETURN_URL,
            $this->riskAssessmentScoreRagClassifier,
            $this->buildSearchParams($pageNr)
        );

        $table = $this->testQualityInformationViewModel->getTable();

        $this->assertEquals($rowsTotalCount, $table->getRowsTotalCount());
        $this->assertEquals($rowsCount, sizeof($table->getData()));
        $this->assertEquals(TestQualityInformationAction::TABLE_MAX_ROW_COUNT, $table->getSearchParams()->getRowsCount());
        $this->assertEquals($pageNr, $table->getSearchParams()->getPageNr());
    }

    public function dataProviderTableValues()
    {
        return [
            [
                'rowsCount' => 5,
                'rowsTotalCount' => 5,
                'pageNr' => 1,
            ],
            [
                'rowsCount' => 10,
                'rowsTotalCount' => 23,
                'pageNr' => 2,
            ],
        ];
    }

    protected function mockConfig()
    {
        $this->config = $this->getMockBuilder(MotConfig::class)->disableOriginalConstructor()->getMock();
        $returnMap = [
            ['site_assessment', 'green', 'start', 0],
            ['site_assessment', 'amber', 'start', 324.11],
            ['site_assessment', 'red', 'start', 459.21],
        ];

        $this->config->expects($this->any())->method('get')->will($this->returnValueMap($returnMap));
    }

    public function buildAuthorisedExaminerSitePerformanceDto($counter = 1)
    {
        $sites = [];
        $maxIteration = $counter;

        if ($counter > 10) {
            $maxIteration = 10;
        }

        for ($i = 1; $i <= $maxIteration; ++$i) {
            $sites[] = $this->getSiteDto();
        }

        $authorisedExaminerSitePerformanceDto = new AuthorisedExaminerSitesPerformanceDto();

        $authorisedExaminerSitePerformanceDto
            ->setSites($sites)
            ->setSiteTotalCount($counter);

        return $authorisedExaminerSitePerformanceDto;
    }

    private function getSiteDto()
    {
        $address = (new AddressDto())
            ->setAddressLine1('addressLine1')
            ->setCountry('Country')
            ->setPostcode('Postcode')
            ->setTown('Town');

        return (new SiteDto())
            ->setId(1)
            ->setAddress($address)
            ->setName('SiteName')
            ->setNumber('SiteNumber')
            ->setRiskAssessmentScore(1.3);
    }

    public function buildSearchParams($pageNr = 1)
    {
        $searchParamsDto = new SearchParamsDto();
        $searchParamsDto
            ->setPageNr($pageNr);

        return $searchParamsDto;
    }
}
