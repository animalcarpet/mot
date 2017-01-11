<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class OdometerReading extends MotApi
{
    const PATH = 'mot-test/{mot_test_id}/odometer-reading';

    public function addNoMeterReadingToTest($token, $mot_test_id)
    {
        return $this->addReading($token, $mot_test_id, 'NO_METER');
    }

    public function addOdometerNotReadToTest($token, $mot_test_id)
    {
        return $this->addReading($token, $mot_test_id, 'NOT_READ');
    }

    public function editOdometerReading($token, $mot_test_id){
        $body = json_encode([
            'value' => 1001,
            'unit' => "mi",
        ]);

        $this->client->request(new Request(
            'POST',
            str_replace('{mot_test_id}', $mot_test_id, self::PATH),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer' . $token],
            $body
        ));
    }

    public function addMeterReading($token, $mot_test_id, $value, $unit)
    {
        $body = json_encode([
            'value' => (integer) $value,
            'unit' => $unit,
            'resultType' => 'OK',
        ]);

        return $this->client->request(new Request(
            'PUT',
            str_replace('{mot_test_id}', $mot_test_id, self::PATH),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token],
            $body
        ));
    }

    /**
     * @param $token
     * @param $mot_test_id
     * @param $resultType
     *
     * @return Response
     */
    private function addReading($token, $mot_test_id, $resultType)
    {
        $body = json_encode([
            'resultType' => $resultType,
        ]);

        return $this->client->request(new Request(
            'PUT',
            str_replace('{mot_test_id}', $mot_test_id, self::PATH),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token],
            $body
        ));
    }
}
