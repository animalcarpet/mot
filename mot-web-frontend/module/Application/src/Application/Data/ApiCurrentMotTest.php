<?php

namespace Application\Data;

use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Calls to api, path:
 *   /person/:id/current-mot-test.
 */
class ApiCurrentMotTest extends ApiResources
{
    /**
     * @param int $personId
     *
     * @return array
     */
    public function getCurrentMotTest($personId)
    {
        $path = $this->personalDetailsUrlBuilder($personId)->toString();

        return $this->restGet($path)['data'];
    }

    private function personalDetailsUrlBuilder($id)
    {
        return UrlBuilder::person($id)->currentMotTest();
    }
}
