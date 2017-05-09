<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationSiteLinkDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;

/**
 * Class OrganisationSitesMapper.
 */
class OrganisationSitesMapper extends DtoMapper
{
    /**
     * @param $organisationId
     *
     * @return VehicleTestingStationDto
     */
    public function fetchAllForOrganisation($organisationId)
    {
        $apiUrl = AuthorisedExaminerUrlBuilder::site($organisationId);

        return $this->get($apiUrl);
    }

    public function fetchAllUnlinkedSites()
    {
        $apiUrl = AuthorisedExaminerUrlBuilder::siteLink();

        return $this->get($apiUrl);
    }

    public function createSiteLink($orgId, $siteNumber)
    {
        $url = AuthorisedExaminerUrlBuilder::siteLink($orgId);

        return $this->post($url, ['siteNumber' => $siteNumber]);
    }

    public function changeSiteLinkStatus($linkId, $status)
    {
        $url = AuthorisedExaminerUrlBuilder::siteLink(null, $linkId);

        return $this->put($url, $status);
    }

    /**
     * @return OrganisationSiteLinkDto
     */
    public function getSiteLink($linkId)
    {
        $url = AuthorisedExaminerUrlBuilder::siteLink(null, $linkId);

        return $this->get($url);
    }
}
