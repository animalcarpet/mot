<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\NotificationUrlBuilder;

class OrderSecurityCardNotificationService
{
    const SECURITY_CARD_ORDER_NOTIFICATION_TEMPLATE_ID = 33;

    private $client;

    private $dateTimeHolder;

    public function __construct(Client $client, DateTimeHolder $dateTimeHolder)
    {
        $this->client = $client;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function sendNotification($recipientId)
    {
        $url = NotificationUrlBuilder::newNotification()->toString();

        $postData = [
            'template' => OrderSecurityCardNotificationService::SECURITY_CARD_ORDER_NOTIFICATION_TEMPLATE_ID,
            'recipient' => $recipientId,
            'fields' => [
                'dateTimeOrdered' => $this->getDateTimeOrdered(),
            ]
        ];

        return $this->client->post($url, $postData);
    }

    private function getDateTimeOrdered()
    {
        $dateTime = $this->dateTimeHolder->getUserCurrent();
        return $dateTime->format('g.ia \o\n j F Y');
    }

}
