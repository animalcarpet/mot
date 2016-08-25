<?php

namespace DvsaClient\Mapper;

use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\SiteUrlBuilder;

/**
 * Class SitePositionMapper
 */
class SitePositionMapper extends Mapper implements BusinessPositionMapperInterface
{

    /**
     * Validate if a person has already been nominated for a role at a Site
     *
     * @param int $siteId
     * @param int $nomineeId
     */
    public function validate($siteId, $nomineeId, $roleCode)
    {
        $data = [
            'nomineeId' => $nomineeId,
            'roleCode' => $roleCode
        ];

        $this->client->postJson(SiteUrlBuilder::site($siteId)->validate()->toString(), $data);
    }

    /**
     * @param int $siteId
     * @param int $nomineeId
     * @param string $roleCode
     */
    public function post($siteId, $nomineeId, $roleCode)
    {
        $url = SiteUrlBuilder::site($siteId)->position()->toString();
        $data = ['nomineeId' => $nomineeId, 'roleCode' => $roleCode];

        $this->client->postJson($url, $data);
    }

    /**
     * @param int $siteId
     * @param int $nomineeId
     * @param string $roleCode
     * @return array
     */
    public function update($siteId, $nomineeId, $roleCode)
    {
        $url = SiteUrlBuilder::site($siteId)->position()->toString();
        $data = ['nomineeId' => $nomineeId, 'roleCode' => $roleCode];

        return $this->client->put($url, $data);
    }

    /**
     * @param int $siteId
     * @param int $positionId
     */
    public function deletePosition($siteId, $positionId)
    {
        $url = SiteUrlBuilder::site($siteId)->position()->routeParam('positionId', $positionId)->toString();
        $this->client->delete($url);
    }
}
