<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryResult\AbstractTesterPerformanceResult;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TesterPerformanceResult extends AbstractTesterPerformanceResult implements ReflectiveDtoInterface
{
    private $person_id;
    private $username;
    private $firstName;
    private $middleName;
    private $familyName;

    public function getPersonId()
    {
        return $this->person_id;
    }

    /**
     * @param $person_id
     * @return TesterPerformanceResult
     */
    public function setPersonId($person_id)
    {
        $this->person_id = $person_id;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $username
     * @return TesterPerformanceResult
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param $firstName
     * @return TesterPerformanceResult
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param $middleName
     * @return TesterPerformanceResult
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
        return $this;
    }

    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @param $familyName
     * @return TesterPerformanceResult
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;
        return $this;
    }

}
