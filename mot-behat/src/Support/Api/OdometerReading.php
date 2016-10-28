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

    public function addMeterReading($token, $mot_test_id, $value, $unit)
    {
        $params = [
            'value' => (integer) $value,
            'unit' => $unit,
            'resultType' => 'OK',
        ];

        return $this->sendPutRequest(
            $token,
            str_replace('{mot_test_id}', $mot_test_id, self::PATH),
            $params
        );
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
        $params = [
            'resultType' => $resultType,
        ];

        return $this->sendPutRequest(
            $token,
            str_replace('{mot_test_id}', $mot_test_id, self::PATH),
            $params
        );
    }
}
