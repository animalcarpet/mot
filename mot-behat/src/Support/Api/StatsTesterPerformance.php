<?php

namespace Dvsa\Mot\Behat\Support\Api;

class StatsTesterPerformance extends MotApi
{
    const PATH_SITE_STATS = "statistic/tester-performance/site/{site_id}/{year}/{month}";
    const PATH_NATIONAL_STATS = "statistic/tester-performance/national/{year}/{month}";
    const PATH_ORGANISATION_STATS = "statistic/tester-performance/authorised-examiner/{ae_id}";

    public function getSiteTesterPerformance($token, $siteId, $year, $month)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace(
                ["{site_id}", "{year}", "{month}"],
                [$siteId, $year, $month],
                self::PATH_SITE_STATS
            )
        );
    }

    public function getNationalTesterPerformance($token, $year, $month)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace(
                ["{year}", "{month}"],
                [$year, $month],
                self::PATH_NATIONAL_STATS
            )
        );
    }

    public function getAuthorisedExaminerTesterPerformance($token, $aeId)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace("{ae_id}", $aeId, self::PATH_ORGANISATION_STATS));
    }
}
