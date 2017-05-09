<?php

namespace Application\Navigation\Breadcrumbs\Handler;

use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;

/**
 * Resolves organisation name based on site id.
 */
class OrganisationNameBySiteResolver extends BreadcrumbsPartResolver
{
    public function __construct($client, $urlHelper)
    {
        $this->client = $client;
        $this->urlHelper = $urlHelper;
    }

    public function resolve($siteId)
    {
        $url = $this->urlHelper;
        $result = $this->client
            ->get(VehicleTestingStationUrlBuilder::vtsOrganisationName($siteId)->toString())['data'];

        if (!empty($result)) {
            return [$result['name'] => $url('authorised-examiner', ['id' => $result['id']])];
        } else {
            return [];
        }
    }
}
