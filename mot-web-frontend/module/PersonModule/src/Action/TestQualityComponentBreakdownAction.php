<?php
namespace Dvsa\Mot\Frontend\PersonModule\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityComponentBreakdownViewModel;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiComponentsBreadcrumbs;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Mvc\Controller\Plugin\Url;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiBreadcrumbs;

class TestQualityComponentBreakdownAction implements AutoWireableInterface
{
    const PAGE_TITLE = "Test quality information";

    private $componentFailRateApiResource;
    private $nationalComponentStatisticApiResource;

    /** @var \DateTime */
    private $viewedDate;
    private $routes;
    private $contextProvider;
    private $testerGroupAuthorisationMapper;
    private $assertion;
    private $breadcrumbs;

    public function __construct(
        ComponentFailRateApiResource $componentFailRateApiResource,
        NationalComponentStatisticApiResource $nationalComponentStatisticApiResource,
        ContextProvider $contextProvider,
        PersonProfileRoutes $routes,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        ViewTesterTestQualityAssertion $assertion,
        TesterTqiComponentsBreadcrumbs $breadcrumbs
    ) {
        $this->componentFailRateApiResource = $componentFailRateApiResource;
        $this->nationalComponentStatisticApiResource = $nationalComponentStatisticApiResource;
        $this->contextProvider = $contextProvider;
        $this->routes = $routes;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->assertion = $assertion;
        $this->breadcrumbs = $breadcrumbs;
    }

    public function execute($testerId, $groupCode, $month, $year, Url $urlPlugin, $requestParams)
    {
        $personAuthorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($testerId);
        $this->assertion->assertGranted($testerId, $personAuthorisation);

        try {
            $this->viewedDate = $this->setMonthAndYear($month, $year);
        } catch (IncorrectDateFormatException $ie) {
            return new NotFoundActionResult();
        }

        $testerBreakdown = $this->componentFailRateApiResource->getForTester($testerId, $groupCode, $month, $year);
        $nationalBreakdown = $this->nationalComponentStatisticApiResource->getForDate($groupCode, $month, $year);

        $returnUrl = $urlPlugin->fromRoute($this->routes->getTestQualityRoute(), $requestParams);

        if ($this->checkIfTesterHasTests($testerBreakdown)) {
            return $this->buildActionResult(
                new TestQualityComponentBreakdownViewModel($testerBreakdown,
                    $nationalBreakdown,
                    $groupCode,
                    $returnUrl,
                    "Return to test quality information"
                ),
                $this->breadcrumbs->getBreadcrumbs($testerId, $month, $year)
            );
        } else {
            return new NotFoundActionResult();
        }
    }

    private function buildActionResult(TestQualityComponentBreakdownViewModel $vm, array $breadcrumbs)
    {
        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('test-quality-information/component-breakdown');
        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);
        $actionResult->layout()->setPageSubTitle($this->getPageSubTitle());
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTertiaryTitle($this->getTertiaryTitle());

        return $actionResult;
    }

    /**
     * @param ComponentBreakdownDto $testerBreakdown
     * @return bool
     */
    private function checkIfTesterHasTests(ComponentBreakdownDto $testerBreakdown)
    {
        return $testerBreakdown->getGroupPerformance()->getTotal() > 0;
    }

    /**
     * @param $month
     * @param $year
     * @return \DateTime
     * @throws IncorrectDateFormatException
     */
    private function setMonthAndYear($month, $year)
    {
        return DateUtils::toDateFromParts(1, $month, $year);
    }

    private function getTertiaryTitle()
    {
        return "Failures by category at all associated sites in " . $this->viewedDate->format("F Y");
    }

    private function isYourProfile()
    {
        return $this->contextProvider->getContext() == ContextProvider::YOUR_PROFILE_CONTEXT;
    }

    private function getPageSubTitle()
    {
        return $this->isYourProfile() ? "Your profile" : "User profile";
    }
}