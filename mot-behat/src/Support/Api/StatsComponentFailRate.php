<?php

namespace Dvsa\Mot\Behat\Support\Api;

class StatsComponentFailRate extends MotApi
{
    const PATH_TESTER_COMPONENT_FAIL_RATE = "statistic/component-fail-rate/site/{site_id}/tester/{tester_id}/group/{group}/{year}/{month}";
    const PATH_NATIONAL_COMPONENT_FAIL_RATE = "statistic/component-fail-rate/national/group/{group}/{year}/{month}";

    public function getTesterComponentFailRate($token, $siteId, $testerId, $group, $year, $month)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace(
                ["{site_id}", "{tester_id}", "{group}", "{year}", "{month}"],
                [$siteId, $testerId, strtoupper($group), $year, $month],
                self::PATH_TESTER_COMPONENT_FAIL_RATE
            )
        );
    }

    public function getNationalComponentFailRate($token, $group, $year, $month)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace(
                ["{group}", "{year}", "{month}"],
                [strtoupper($group), $year, $month],
                self::PATH_NATIONAL_COMPONENT_FAIL_RATE
            )
        );
    }
}
