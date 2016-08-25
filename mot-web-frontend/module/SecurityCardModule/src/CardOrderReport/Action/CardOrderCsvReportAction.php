<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action;

use Core\Action\HttpResponseResult;
use Core\Action\NotFoundActionResult;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaCommon\Date\DateTimeHolder;
use DateInterval;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use Zend\Http\Headers;
use Zend\Http\Response\Stream;

class CardOrderCsvReportAction
{
    const DATE_INTERVAL_10_HOURS = 'PT10H';
    const DATE_INTERVAL_1_HOUR = 'PT1H';
    const DATE_INTERVAL_ONE_WEEK = 'P7D';
    const DATE_INTERVAL_ONE_DAY = 'P1D';
    const ONE_HOUR_IN_SECS = 3600;

    private $resourcePath;

    private $authService;

    private $authorisationServiceClient;

    private $featureToggle;

    /** @var DateTimeHolder $dateTimeHolder */
    private $dateTimeHolder;

    public function __construct(
        $resourcePath,
        MotFrontendAuthorisationServiceInterface $authService,
        AuthorisationService $authorisationServiceClient,
        TwoFaFeatureToggle $featureToggle,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->resourcePath = $resourcePath;
        $this->authService = $authService;
        $this->authorisationServiceClient = $authorisationServiceClient;
        $this->featureToggle = $featureToggle;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function execute($dateRouteParam)
    {
        if (!$this->featureToggle->isEnabled()) {
            return new NotFoundActionResult();
        }
        $this->authService->assertGranted(PermissionInSystem::VIEW_SECURITY_CARD_ORDER);

        if (!(bool)strtotime($dateRouteParam)) {
            return new NotFoundActionResult();
        }

        $nowAsTime = $this->dateTimeHolder->getUserCurrent();
        $tenAmToday =$this->dateTimeHolder->getUserCurrentDate()
            ->setTime(0,0,0)
            ->add(new DateInterval(self::DATE_INTERVAL_10_HOURS));

        $minDate = $this->dateTimeHolder->getUserCurrentDate()
            ->sub(new DateInterval(self::DATE_INTERVAL_ONE_WEEK))
            ->add(new DateInterval(self::DATE_INTERVAL_10_HOURS));
        $maxDate = $this->dateTimeHolder->getUserCurrentDate()
            ->sub(new DateInterval(self::DATE_INTERVAL_ONE_DAY))
            ->add(new DateInterval(self::DATE_INTERVAL_10_HOURS));

        if ($nowAsTime < $tenAmToday)
        {
            $minDate->sub(new DateInterval(self::DATE_INTERVAL_ONE_DAY));
            $maxDate->sub(new DateInterval(self::DATE_INTERVAL_ONE_DAY));
        }

        $fromDate = DateUtils::toDateTime($dateRouteParam);
        $toDate = clone $fromDate;
        $toDate->add(new DateInterval(self::DATE_INTERVAL_ONE_DAY));
        if (date_offset_get($this->dateTimeHolder->getUserCurrent()) === self::ONE_HOUR_IN_SECS)
        {
            $fromDate->sub(new DateInterval(self::DATE_INTERVAL_1_HOUR));
            $minDate->sub(new DateInterval(self::DATE_INTERVAL_1_HOUR));
            $toDate->sub(new DateInterval(self::DATE_INTERVAL_1_HOUR));
            $maxDate->sub(new DateInterval(self::DATE_INTERVAL_1_HOUR));
        }

        if ($fromDate->format(DateUtils::FORMAT_ISO_WITH_TIME) < $minDate->format(DateUtils::FORMAT_ISO_WITH_TIME)
            || $fromDate->format(DateUtils::FORMAT_ISO_WITH_TIME) > $maxDate->format(DateUtils::FORMAT_ISO_WITH_TIME)) {
            return new NotFoundActionResult();
        }

        $orders = $this->authorisationServiceClient->getSecurityCardOrdersInDateRange($fromDate, $toDate);
        $streamResource = $this->buildCsv($orders);
        $response = $this->buildResponseStream($streamResource, $dateRouteParam);

        return new HttpResponseResult($response);
    }

    /**
     * @param Collection $orders
     * @return resource
     */
    private function buildCsv(Collection $orders)
    {
        $resource = fopen($this->resourcePath, 'w');

        fputcsv($resource, [
            'Submitted on',
            'Full name',
            'Recipient name',
            'Address line 1',
            'Address line 2',
            'Address line 3',
            'Town',
            'Postcode'
        ]);

        /** @var SecurityCardOrder $order */
        foreach ($orders->getAll() as $order) {
            fputcsv($resource, [
                $order->getSubmittedOn(),
                $order->getFullName(),
                $order->getRecipientName(),
                $order->getAddressLine1(),
                $order->getAddressLine2(),
                $order->getAddressLine3(),
                $order->getTown(),
                $order->getPostcode()
            ]);
        }

        rewind($resource);

        return $resource;
    }

    /**
     * @param resource $streamResource
     * @param string $dateRouteParam
     * @return Stream
     */
    private function buildResponseStream($streamResource, $dateRouteParam)
    {
        $response = new Stream();
        $response->setStream($streamResource);
        $headers = new Headers();
        $headers->addHeaders([
            'Content-Disposition' => "attachment; filename={$dateRouteParam}-security-card-orders.csv",
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
        $response->setHeaders($headers);

        return $response;
    }
}
