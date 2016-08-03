<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto;

class EmployeePerformanceDto extends MotTestingPerformanceDto
{
    private $username;
    private $personId;

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
}
