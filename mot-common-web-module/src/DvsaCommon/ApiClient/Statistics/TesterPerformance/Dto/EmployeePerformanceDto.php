<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

class EmployeePerformanceDto extends MotTestingPerformanceDto
{
    private $username;
    private $personId;
    private $firstName;
    private $middleName;
    private $familyName;

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPersonId()
    {
        return $this->personId;
    }

    public function setPersonId($personId)
    {
        $this->personId = $personId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return EmployeePerformanceDto
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     * @return EmployeePerformanceDto
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @param string $familyName
     * @return EmployeePerformanceDto
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;
        return $this;
    }
}
