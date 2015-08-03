<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class Tester extends MotApi
{
    const PATH_TESTER = 'tester/{user_id}';
    const PATH_DEMO_TEST_ASSESSMENT = 'demo-test-assessment';
    const PATH_TESTER_TEST_LOGS = 'tester/{user_id}/mot-test-log';
    const PATH_TESTER_TEST_LOGS_SUMMARY = 'tester/{user_id}/mot-test-log/summary';

    public function getTesterDetails($token, $user_id)
    {
        return $this->client->request(new Request(
            MotApi::METHOD_GET,
            str_replace('{user_id}', $user_id, self::PATH_TESTER),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token]
        ));
    }

    public function getTesterTestLogsSummary($token, $user_id)
    {
        return $this->client->request(new Request(
            'GET',
            str_replace('{user_id}', $user_id, self::PATH_TESTER_TEST_LOGS_SUMMARY),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token]
        ));
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
            self::PATH_DEMO_TEST_ASSESSMENT,
            $data
        );
    }

    public function getTesterTestLogs($token, $user_id)
    {
        return $this->client->request(
            new Request(
                'POST',
                str_replace('{user_id}', $user_id, self::PATH_TESTER_TEST_LOGS),
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                '{"format":"DATA_TABLES","_class":"DvsaCommon\\\\Dto\\\\Search\\\\MotTestSearchParamsDto"}'
            )
        );
    }
}
