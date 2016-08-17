<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action;


use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationMonthFilter;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation\TestQualityInformationViewModel;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterPerformanceApiResource;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Mvc\Controller\Plugin\Url;

class TestQualityAction implements AutoWireableInterface
{
    const SUBTITLE_YOUR_PROFILE = 'Your profile';
    const SUBTITLE_USER_PROFILE = 'User profile';
    const COMPONENT_BREAKDOWN_LINK_TEXT = 'View %sGroup %s failures by category in %s %s';

    /** @var  TesterPerformanceApiResource $testerPerformanceApiResource */
    private $testerPerformanceApiResource;

    /** @var  NationalPerformanceApiResource $nationalPerformanceApiResource */
    private $nationalPerformanceApiResource;

    /** @var  bool $isYourProfile */
    private $isYourProfile;

    /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;

    /** @var ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion */
    private $viewTesterTestQualityAssertion;

    /** @var PersonProfileRoutes $personProfileRoutes */
    private $personProfileRoutes;

    public function __construct(
        TesterPerformanceApiResource $testerPerformanceApiResource,
        NationalPerformanceApiResource $nationalPerformanceApiResource,
        ContextProvider $contextProvider,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        ViewTesterTestQualityAssertion $viewTesterTestQualityAssertion,
        PersonProfileRoutes $personProfileRoutes
    )
    {
        $this->testerPerformanceApiResource = $testerPerformanceApiResource;
        $this->nationalPerformanceApiResource = $nationalPerformanceApiResource;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->viewTesterTestQualityAssertion = $viewTesterTestQualityAssertion;
        $this->personProfileRoutes = $personProfileRoutes;
        $this->setYourProfileFlag($contextProvider->getContext());
    }

    /**
     * @param int $targetPersonId
     * @param int $month
     * @param int $year
     * @param Url $url
     * @param $requestParams
     * @param array $breadcrumbs
     * @return ActionResult
     */
    public function execute(
        $targetPersonId,
        $month,
        $year,
        Url $url,
        $requestParams,
        array $breadcrumbs
    )
    {
        $personAuthorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($targetPersonId);
        $this->viewTesterTestQualityAssertion->assertGranted($targetPersonId, $personAuthorisation);

        /** @var TesterPerformanceDto $testerPerformance */
        $testerPerformance = $this->testerPerformanceApiResource->get($targetPersonId, $month, $year);
        /** @var NationalPerformanceReportDto $nationalPerformance */
        $nationalPerformance = $this->nationalPerformanceApiResource->getForDate($month, $year);
        try {
            $date = DateUtils::toDateFromParts(1, $month, $year);
        } catch (IncorrectDateFormatException $ie) {
            return new NotFoundActionResult();
        }
        $returnLink = $url->fromRoute($this->personProfileRoutes->getRoute(), $requestParams);

        $componentBreakdownLinkText = sprintf(
            self::COMPONENT_BREAKDOWN_LINK_TEXT,
            $this->isYourProfile ? 'your ' : '',
            '%s',
            $this->getMonthFullName($date),
            $year
        );

        return $this->buildActionResult(
            new TestQualityInformationViewModel(
                $testerPerformance,
                $nationalPerformance,
                $personAuthorisation,
                $date,
                $returnLink,
                $this->getReturnLinkText(),
                $componentBreakdownLinkText,
                $this->getMonthFilter($requestParams, $url)
            ),
            $this->getMonthFullName($date),
            $year,
            $breadcrumbs
        );

    }

    /**
     * @param $vm
     * @param string $monthFullName
     * @param string $year
     * @param array $breadcrumbs
     * @return ActionResult
     */
    private function buildActionResult($vm, $monthFullName, $year, $breadcrumbs)
    {
        $actionResult = new ActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->setTemplate('test-quality-information/view');

        $actionResult->layout()->setPageSubTitle($this->getProfileDescription());
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setPageTitle('Test quality information');
        $actionResult->layout()->setPageLede(sprintf(
                'Tests done at all associated sites in %s %s',
                $monthFullName,
                $year
            )
        );

        return $actionResult;
    }

    private function setYourProfileFlag($context)
    {
        $this->isYourProfile = (ContextProvider::YOUR_PROFILE_CONTEXT === $context);
    }

    private function getProfileDescription()
    {
        return $this->isYourProfile ? self::SUBTITLE_YOUR_PROFILE : self::SUBTITLE_USER_PROFILE;
    }

    private function getReturnLinkText()
    {
        return 'Return to ' . strtolower($this->getProfileDescription());
    }
    private function getMonthFullName(\DateTime $date)
    {
        return $date->format('F');
    }

    /**
     * @param $params
     * @param $url
     * @return TestQualityInformationMonthFilter
     */
    private function getMonthFilter($params, $url)
    {
        return new TestQualityInformationMonthFilter($this->personProfileRoutes, $params, $url);
    }
}