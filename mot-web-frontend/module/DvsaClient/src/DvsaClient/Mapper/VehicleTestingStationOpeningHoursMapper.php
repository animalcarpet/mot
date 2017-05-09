<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\SiteDailyOpeningHours;
use DvsaCommon\Date\Time;
use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Class VehicleTestingStationOpeningHoursMapper.
 */
class VehicleTestingStationOpeningHoursMapper extends Mapper
{
    protected $entityClass = SiteDailyOpeningHours::class;

    public function update($siteId, $data)
    {
        $jsonArray = $this->encodeFromDataToJson($data);

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', $siteId)->siteOpeningHours();
        $this->client->putJson($apiUrl, $jsonArray);
    }

    private function encodeFromDataToJson($data)
    {
        $jsonArray = [];
        $days = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        foreach ($days as $dayAsInt => $day) {
            $openTime12h = isset($data[$day.'OpenTime']) ? $data[$day.'OpenTime'] : '';
            $closeTime12h = isset($data[$day.'CloseTime']) ? $data[$day.'CloseTime'] : '';

            if ($data[$day.'IsClosed']) {
                $openTime = '';
                $closeTime = '';
            } else {
                $openTimePeriod = isset($data[$day.'OpenTimePeriod']) ? $data[$day.'OpenTimePeriod'] : '';
                $closeTimePeriod = isset($data[$day.'CloseTimePeriod']) ? $data[$day.'CloseTimePeriod'] : '';

                $openTime = $this->toIsoFormat($openTime12h.$openTimePeriod);
                $closeTime = $this->toIsoFormat($closeTime12h.$closeTimePeriod);
            }

            $jsonArray['weeklySchedule'][] = [
                'weekday' => $dayAsInt,
                'openTime' => $openTime,
                'closeTime' => $closeTime,
                'isClosed' => $data[$day.'IsClosed'] === 'true',
            ];
        }

        return $jsonArray;
    }

    private function toIsoFormat($time12h)
    {
        $splitTime = [];
        $timeFormatIsValid = preg_match('/^(0[1-9]|1[0-2]|[1-9])[\.:]?([0-5][0-9])?([ap]m)$/', $time12h, $splitTime);

        if ($timeFormatIsValid) {
            if (empty($splitTime[2])) {
                $dateTime = \DateTime::createFromFormat('h a', $splitTime[1].$splitTime[3]);
            } else {
                $dateTime = \DateTime::createFromFormat('h:i a', $splitTime[1].':'.$splitTime[2].$splitTime[3]);
            }
            $timeObject = Time::fromDateTime($dateTime);
            $isoTime = $timeObject->toIso8601();
        } else {
            $isoTime = $time12h;
        }

        return $isoTime;
    }
}
