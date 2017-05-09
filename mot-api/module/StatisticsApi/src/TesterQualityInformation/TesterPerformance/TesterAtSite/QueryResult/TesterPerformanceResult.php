<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryResult\AbstractTesterPerformanceResult;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterPerformanceResult extends AbstractTesterPerformanceResult implements ReflectiveDtoInterface
{
    private $person_id;
    private $username;

    public function getPersonId()
    {
        return $this->person_id;
    }

    public function setPersonId($person_id)
    {
        $this->person_id = $person_id;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }
}
