<?php

namespace Dashboard;

use Application\Data\ApiPersonalDetails;

/**
 * Class PersonStore.
 */
class PersonStore
{
    private $apiPersonalDetails;

    public function __construct(ApiPersonalDetails $apiPersonalDetails)
    {
        $this->apiPersonalDetails = $apiPersonalDetails;
    }

    public function get($personId)
    {
        return $this->apiPersonalDetails->getPersonalDetailsData($personId);
    }

    public function update($personId, $data)
    {
        return $this->apiPersonalDetails->updatePersonalDetailsData($personId, $data);
    }

    public function updatePersonalAuthorisationForMotTesting($personId, $data)
    {
        return $this->apiPersonalDetails->updatePersonalAuthorisationForMotTesting($personId, $data);
    }
}
