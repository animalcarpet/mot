<?php

namespace Application\Navigation\Breadcrumbs\Handler;

use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;

/**
 * Resolves site name by site id.
 */
class SiteNameResolver extends BreadcrumbsPartResolver
{
    public function __construct($client, $urlHelper)
    {
        $this->client = $client;
        $this->urlHelper = $urlHelper;
    }

    public function resolve($siteId)
    {
        $result = $this->client->get(VehicleTestingStationUrlBuilder::vtsName($siteId)->toString());
        $url = $this->urlHelper;

        return [$result['data'] => $url('vehicle-testing-station', ['id' => $siteId])];
    }
}
