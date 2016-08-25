<?php

namespace Application\Data;

use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Calls to api, path:
 *   /personal-details/id[/mot-testing].
 */
class ApiPersonalDetails extends ApiResources
{
    /**
     * @param int $personId
     *
     * @return array
     */
    public function getPersonalDetailsData($personId)
    {
        $path = UrlBuilder::personalDetails($personId)->toString();
        try {
            $data = $this->restGet($path)['data'];
        } catch (NotFoundException $e) {
            $data = [];
        }

        return $data;
    }

    /**
     * @param int   $personId
     * @param array $data
     *
     * @return array
     */
    public function updatePersonalDetailsData($personId, $data)
    {
        $path = UrlBuilder::personalDetails($personId)->toString();

        return $this->restUpdate($path, $data)['data'];
    }

    /**
     * @param int   $personId
     * @param array $data
     *
     * @return array
     */
    public function updatePersonalAuthorisationForMotTesting($personId, $data)
    {
        $path = PersonUrlBuilder::motTesting($personId)->toString();

        return $this->restUpdate($path, $data)['data'];
    }

    /**
     * @param int $personId
     *
     * @return array
     */
    public function getPersonalAuthorisationForMotTesting($personId)
    {
        $path = PersonUrlBuilder::motTesting($personId)->toString();

        return $this->restGet($path)['data'];
    }

    /**
     * @param int $personId
     *
     * @return array
     */
    public function getPendingRolesForPerson($personId)
    {
        $path = UrlBuilder::pendingRoles($personId)->toString();

        return $this->restGet($path)['data'];
    }
}
