<?php

namespace TestSupport\Service;

use DvsaCommon\UrlBuilder\UrlBuilder;
use TestSupport\Helper\TestSupportRestClientHelper;

class StatisticsDbService
{
    /**
     * @var TestSupportRestClientHelper
     */
    private $restClientHelper;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    public function __construct(
        TestSupportRestClientHelper $restClientHelper,
        UrlBuilder $urlBuilder
    ) {
        $this->restClientHelper = $restClientHelper;
        $this->urlBuilder = $urlBuilder;
    }

    public function createStatisticsDbTests($monthsAgo)
    {
        $response = $this->restClientHelper->getJsonClient([])->get($this->urlBuilder::of()->generateStatisticsDbTests()->queryParam('monthsAgo', $monthsAgo)->toString());

        if ($response['data']['success'] == true) {
            return true;
        }

        return false;
    }

    public function createStatisticsDbRfr($monthsAgo)
    {
        $response = $this->restClientHelper->getJsonClient([])->get($this->urlBuilder::of()->generateStatisticsDbRfr()->queryParam('monthsAgo', $monthsAgo)->toString());

        if ($response['data']['success'] == true) {
            return true;
        }

        return false;
    }
}
