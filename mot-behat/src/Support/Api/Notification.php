<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\HttpClient;

class Notification extends MotApi
{
    const PATH = '/notification/person/';
    const TQI_SITE_MANAGER_NOTIFICATION_PATH = '/statistic/tester-quality-information/notification/sm';
    const TQI_AEDM_NOTIFICATION_PATH = '/statistic/tester-quality-information/notification/aedm';

    /**
     * @param string $token
     * @param int $personId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function fetchNotificationForPerson($token, $personId)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH.$personId
        );
    }

    public function sendSiteManagerTqiNotifications($token)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::TQI_SITE_MANAGER_NOTIFICATION_PATH
        );
    }

    public function sendAEDMTqiNotifications($token)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::TQI_AEDM_NOTIFICATION_PATH
        );
    }
}
