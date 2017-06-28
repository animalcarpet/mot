<?php

namespace Organisation\Action;

use Core\Action\ActionResult;
use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Core\Routing\AeRoutes;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\AuthorisedExaminerSitePerformanceApiResource;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Organisation\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use Site\Service\RiskAssessmentScoreRagClassifier;
use Zend\View\Helper\Url;

class TestQualityInformationAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Service reports';
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
        $organisation = $this->organisationMapper->getAuthorisedExaminer($organisationId);
        $breadcrumbs = $this->prepareBreadcrumbsResult($organisation, $this->url);

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
                    $this->searchParams,
                    $orgSitePerformance->getSiteTotalCount()
                ),
                $breadcrumbs,
                $organisation->getName()
            );
        } else {
            return new NotFoundActionResult();
        }
    }

    /**
     * @param TestQualityInformationViewModel $vm
     * @param array $breadcrumbs
     * @param string $organisationName
     * @return ViewActionResult
     */
    private function buildActionResult(
        TestQualityInformationViewModel $vm,
        array $breadcrumbs,
        $organisationName
    )
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->layout()->setPageSubTitle($organisationName);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);

        return $actionResult;
    }

    private function prepareBreadcrumbsResult(OrganisationDto $organisation, $urlPlugin)
    {
        $this->setOrganisationLink(AeRoutes::of($urlPlugin)->ae($organisation->getId()));

        return [
            $organisation->getName() => $this->getOrganisationLink(),
            self::PAGE_TITLE => '',
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
