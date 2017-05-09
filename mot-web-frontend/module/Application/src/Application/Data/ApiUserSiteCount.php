<?php

namespace Application\Data;

use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Calls to api, path:
 *   /person/:id/site-count.
 */
class ApiUserSiteCount extends ApiResources
{
    /**
     * @param int $personId
     *
     * @return array
     */
    public function getUserSiteCount($personId)
    {
        $path = $this->personalDetailsUrlBuilder($personId)->toString();

        return $this->restGet($path)['data'];
    }

    /**
     * Gets the API Endpoint URL for the site count.
     *
     * @param $id
     *
     * @return $this
     */
    private function personalDetailsUrlBuilder($id)
    {
        return UrlBuilder::person($id)->getSiteCount();
    }
}
