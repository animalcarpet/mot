<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Request;
use DvsaCommon\Date\DateUtils;

class ContingencyTest extends MotApi
{
    const PATH = 'emergency-log';
    const PATH_START_CONT_TEST = 'mot-test';

    /**
     * @param $token
     * @param string $contingencyCode
     * @param string $reasonCode
     *
     * @return array
     */
    public function getContingencyCodeID($token, $contingencyCode, $reasonCode, \DateTime $dateTime = null, $siteId = 1)
    {
        //use generic password if $password is "default"
        $contingencyCode = strcasecmp($contingencyCode, 'DEFAULT') == 0 ? Authentication::CONTINGENCY_CODE_DEFAULT : $contingencyCode;
        $otherReasonText = 'some reason text';

        if (is_null($dateTime)) {
            $dateTime = DateUtils::today();
        }

        $body = json_encode([
            'siteId'            => (string) $siteId,
            'contingencyCode'   => $contingencyCode,
            'performedAtYear'   => $dateTime->format('Y'),
            'performedAtMonth'  => $dateTime->format('m'),
            'performedAtDay'    => $dateTime->format('d'),
            'performedAtHour'   => $dateTime->format('g'),
            'performedAtMinute' => $dateTime->format('i'),
            'performedAtAmPm'   => $dateTime->format('a'),
            'reasonCode'        => $reasonCode,
            'otherReasonText'   => $otherReasonText,
            '_class'            => '\\DvsaCommon\\Dto\MotTesting\\ContingencyTestDto',
        ]);

        return $this->client->request(new Request(
            'POST',
            self::PATH,
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $token],
            $body
        ));
    }

    public function startContingencyTest($token, $contingencyId, $vehicleId, $vehicleClass, $siteId = 1)
    {
        $today =  DateUtils::today();

        $body = json_encode([
            'vehicleId'               => $vehicleId,
            'vehicleTestingStationId' => $siteId,
            'primaryColour'           => 'C',
            'secondaryColour'         => 'C',
            'fuelTypeId'              => 'PE',
            'vehicleClassCode'        => $vehicleClass,
            'hasRegistration'         => true,
            'oneTimePassword'         => Authentication::ONE_TIME_PASSWORD,
            'contingencyId'           => $contingencyId,
            'contingencyDto'          => [
                    'contingencyCode'   => Authentication::CONTINGENCY_CODE_DEFAULT,
                    'reasonCode'        => 'SO',
                    'otherReasonText'   => '',
                    'siteId'            => $siteId,
                    'testType'          => 'normal',
                    'performedAtYear'   => $today->format('Y'),
                    'performedAtMonth'  => $today->format('m'),
                    'performedAtDay'    => $today->format('d'),
                    'performedAtHour'   => $today->format('g'),
                    'performedAtMinute' => $today->format('i'),
                    'performedAtAmPm'   => $today->format('a'),
                    '_class'            => 'DvsaCommon\\Dto\\MotTesting\\ContingencyTestDto',
                ],
        ]);

        return $this->client->request(new Request(
            'POST',
            self::PATH_START_CONT_TEST,
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $token],
            $body
        ));
    }

    public function startContingencyTestOnDateAtTime($token, $contingencyId, $vehicleId, $vehicleClass, \DateTime $dateTime)
    {
        $body = json_encode([
            'vehicleId'               => $vehicleId,
            'vehicleTestingStationId' => 1,
            'primaryColour'           => 'C',
            'secondaryColour'         => 'C',
            'fuelTypeId'              => 'PE',
            'vehicleClassCode'        => $vehicleClass,
            'hasRegistration'         => true,
            'oneTimePassword'         => Authentication::ONE_TIME_PASSWORD,
            'contingencyId'           => $contingencyId,
            'contingencyDto'          => [
                'contingencyCode'   => Authentication::CONTINGENCY_CODE_DEFAULT,
                'reasonCode'        => 'SO',
                'otherReasonText'   => '',
                'siteId'            => '1',
                'testType'          => 'normal',
                'performedAtYear'   => $dateTime->format('Y'),
                'performedAtMonth'  => $dateTime->format('m'),
                'performedAtDay'    => $dateTime->format('d'),
                'performedAtHour'   => $dateTime->format('g'),
                'performedAtMinute' => $dateTime->format('i'),
                'performedAtAmPm'   => $dateTime->format('a'),
                '_class'            => 'DvsaCommon\\Dto\\MotTesting\\ContingencyTestDto',
            ],
        ]);

        return $this->client->request(new Request(
            'POST',
            self::PATH_START_CONT_TEST,
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $token],
            $body
        ));
    }
}
