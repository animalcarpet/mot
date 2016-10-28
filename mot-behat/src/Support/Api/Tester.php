<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class Tester extends MotApi
{
    const PATH_TESTER = 'tester/{user_id}';
    const PATH_DEMO_TEST_ASSESSMENT = 'person/{person_id}/demo-test-assessment';
    const PATH_TESTER_TEST_LOGS = 'tester/{user_id}/mot-test-log';
    const PATH_TESTER_TEST_LOGS_SUMMARY = 'tester/{user_id}/mot-test-log/summary';
    const PATH_TESTER_FULL = 'tester/full';

    public function getTesterDetails($token, $user_id)
    {
        return $this->sendGetRequest(
            $token,
            str_replace('{user_id}', $user_id, self::PATH_TESTER)
        );
    }

    public function getTesterTestLogsSummary($token, $user_id)
    {
        return $this->sendGetRequest(
            $token,
            str_replace('{user_id}', $user_id, self::PATH_TESTER_TEST_LOGS_SUMMARY)
        );
    }

    /**
     * @param string $token
     * @param string $group
     * @param int $personId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function updateTesterQualification($token, $group, $personId)
    {
        $data = [
            'vehicleClassGroup' => $group,
            'testerId' => $personId
        ];
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            str_replace('{person_id}', $personId, self::PATH_DEMO_TEST_ASSESSMENT),
            $data
        );
    }

    public function getTesterTestLogs($token, $user_id)
    {
        return $this->sendPostRequest(
            $token,
            str_replace('{user_id}', $user_id, self::PATH_TESTER_TEST_LOGS),
            [
                "format" => "DATA_TABLES",
                "_class" => "DvsaCommon\\Dto\\Search\\MotTestSearchParamsDto"
            ]
        );
    }

    public function getTesterFull($token, $search, $searchBy = "username")
    {
        $params = [
            "format" => "TYPE_AHEAD",
            "search_by" =>  $searchBy,
            "search" => $search
        ];

        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH_TESTER_FULL . "?" . http_build_query($params)
        );
    }
}
