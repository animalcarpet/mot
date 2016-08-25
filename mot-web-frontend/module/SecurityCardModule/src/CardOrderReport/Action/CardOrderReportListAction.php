<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action;

use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use DateInterval;
use DateTime;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardDailyCount;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use Zend\Http\Headers;
use Zend\Http\Response\Stream;
use Zend\View\Model\ViewModel;

class CardOrderReportListAction
{
    const DATE_FORMAT = 'Y-m-d';
    const DATE_SPEC_TOMORROW = 'tomorrow';
    const DATE_INTERVAL_ONE_WEEK = 'P7D';
    const DATE_INTERVAL_ONE_DAY = 'P1D';
    const DATE_INTERVAL_10_HOURS = 'PT10H';
    const DATE_INTERVAL_1_HOUR = 'PT1H';
    const ONE_HOUR_IN_SECS = 3600;

    private $authService;

    private $authorisationServiceClient;

    private $featureToggle;

    /** @var DateTimeHolder $dateTimeHolder */
    private $dateTimeHolder;

    public function __construct(
        MotFrontendAuthorisationServiceInterface $authService,
        AuthorisationService $authorisationServiceClient,
        TwoFaFeatureToggle $featureToggle,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->authService = $authService;
        $this->authorisationServiceClient = $authorisationServiceClient;
        $this->featureToggle = $featureToggle;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * @return ActionResult|NotFoundActionResult
     */
    public function execute()
    {
        if (!$this->featureToggle->isEnabled()) {
            return new NotFoundActionResult();
        }
        $this->authService->assertGranted(PermissionInSystem::VIEW_SECURITY_CARD_ORDER);

        $nowAsTime = $this->dateTimeHolder->getUserCurrent();
        $tenAmToday =$this->dateTimeHolder->getUserCurrentDate()
            ->setTime(0,0,0)
            ->add(new DateInterval(self::DATE_INTERVAL_10_HOURS));

        $clonedNowAsTime = clone $nowAsTime;
        if ($nowAsTime < $tenAmToday)
        {
            $toDate = $clonedNowAsTime
                ->setTime(0,0,0)
                ->sub(new DateInterval(self::DATE_INTERVAL_ONE_DAY))
                ->add(new DateInterval(self::DATE_INTERVAL_10_HOURS));
        } else {
            $toDate = $tenAmToday;
        }
        $clonedToDate = clone $toDate;
        $fromDate = $clonedToDate->sub(new DateInterval(self::DATE_INTERVAL_ONE_WEEK));

        $useHourOffset = date_offset_get($this->dateTimeHolder->getUserCurrent()) === self::ONE_HOUR_IN_SECS;
        if ($useHourOffset)
        {
            $fromDate->sub(new DateInterval(self::DATE_INTERVAL_1_HOUR));
            $toDate->sub(new DateInterval(self::DATE_INTERVAL_1_HOUR));
        }

        $dailyCounts = $this->authorisationServiceClient
            ->getSecurityCardOrderCount($fromDate, $toDate);

        /** @var SecurityCardDailyCount $dailyCount */
        $rows = [];
        foreach ($dailyCounts->getAll() as $dailyCount) {
            $actDate = new \DateTime($dailyCount->getActivationDate());
            if ($useHourOffset)
            {
                $actDate = $actDate->add(new DateInterval(self::DATE_INTERVAL_1_HOUR));
            }
            $rows[] = ['date' => $actDate->format(DateUtils::DATETIME_FORMAT), 'count' => $dailyCount->getCount()];
        }

        $result = (new ActionResult())
            ->setTemplate('2fa/card-order-report/list')
            ->setViewModel(['rows' => $rows]);
        $result->layout()->setBreadcrumbs(['List of ordered security cards' => null]);

        return $result;
    }
}
