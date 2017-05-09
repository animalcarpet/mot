<?php

namespace Organisation\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Core\Routing\AeRoutes;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\AuthorisedExaminerSitePerformanceApiResource;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Organisation\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use Site\Service\RiskAssessmentScoreRagClassifier;
use Zend\View\Helper\Url;

class TestQualityInformationAction implements AutoWireableInterface
{
    const PAGE_SUBTITLE = 'Test quality information';
    const PAGE_TITLE = 'Vehicle testing stations';
    const NO_SITES = 'No active site associations';
    const TABLE_MAX_ROW_COUNT = 10;

    /**
     * @var OrganisationMapper
     */
    private $organisationMapper;

    /**
     * @var AuthorisedExaminerSitePerformanceApiResource
     */
    private $orgSitePerformanceApiResource;

    private $organisationLink;
    /** @var MotConfig $config */
    private $config;

    /** @var SearchParamsDto $searchParams */
    private $searchParams;

    /** @var Url $url */
    private $url;

    public function __construct(
        OrganisationMapper $organisationMapper,
        AuthorisedExaminerSitePerformanceApiResource $orgSitePerformanceApiResource,
        MotConfig $config,
        Url $url
    ) {
        $this->organisationMapper = $organisationMapper;
        $this->orgSitePerformanceApiResource = $orgSitePerformanceApiResource;
        $this->config = $config;
        $this->url = $url;
    }

    /*
     * @return AbstractActionResult
     */
    public function execute($organisationId, $page)
    {
        $breadcrumbs = $this->prepareBreadcrumbsResult($organisationId, $this->url);

        if ($page > 0) {
            $orgSitePerformance = $this->orgSitePerformanceApiResource->getData($organisationId, $page, self::TABLE_MAX_ROW_COUNT);

            $riskAssessmentScoreRagClassifier = new RiskAssessmentScoreRagClassifier(0, $this->config);

            $this->searchParams = (new SearchParamsDto())
                ->setRowsCount(self::TABLE_MAX_ROW_COUNT)
                ->setPageNr($page);

            return $this->buildActionResult(
                new TestQualityInformationViewModel(
                    $orgSitePerformance,
                    $this->getOrganisationLink(),
                    $riskAssessmentScoreRagClassifier,
                    $this->searchParams
                ),
                $breadcrumbs,
                $orgSitePerformance->getSiteTotalCount()
            );
        } else {
            return new NotFoundActionResult();
        }
    }

    /*
     * @return ActionResult
     */
    private function buildActionResult(TestQualityInformationViewModel $vm, array $breadcrumbs, $orgSiteCount)
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->layout()->setPageSubTitle(self::PAGE_SUBTITLE);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTitle($orgSiteCount ? self::PAGE_TITLE : self::NO_SITES);

        return $actionResult;
    }

    private function prepareBreadcrumbsResult($organisationId, $urlPlugin)
    {
        $this->setOrganisationLink(AeRoutes::of($urlPlugin)->ae($organisationId));
        $organisationName = $this->organisationMapper->getAuthorisedExaminer($organisationId)->getName();

        return [
            $organisationName => $this->getOrganisationLink(),
            self::PAGE_SUBTITLE => '',
        ];
    }

    private function getOrganisationLink()
    {
        return $this->organisationLink;
    }

    private function setOrganisationLink($organisationLink)
    {
        $this->organisationLink = $organisationLink;
    }
}
