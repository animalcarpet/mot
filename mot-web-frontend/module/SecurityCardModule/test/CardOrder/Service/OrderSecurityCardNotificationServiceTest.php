<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardNotificationService;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\NotificationUrlBuilder;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class OrderSecurityCardNotificationServiceTest extends PHPUnit_Framework_TestCase
{
    const DATETIME = '2014-01-13 10:00:00.000000';
    const DATETIME_STRING = '10.00am on 13 January 2014';

    public function testApiRequestIsCorrectlyFormed()
    {
        $dateTimeHolder = XMock::of(DateTimeHolder::class);

        $recipientId = 43;
        $expectedUrl = NotificationUrlBuilder::newNotification()->toString();
        $expectedPostData = [
            'template' => OrderSecurityCardNotificationService::SECURITY_CARD_ORDER_NOTIFICATION_TEMPLATE_ID,
            'recipient' => $recipientId,
            'fields' => [
                'dateTimeOrdered' => self::DATETIME_STRING,
            ]
        ];

        $dateTimeHolder
            ->expects($this->once())
            ->method('getUserCurrent')
            ->willReturn(new \DateTime(self::DATETIME));

        $client = XMock::of(Client::class);
        $client
            ->expects($this->once())
            ->method('post')
            ->with($expectedUrl, $expectedPostData);

        $service = new OrderSecurityCardNotificationService($client, $dateTimeHolder);
        $service->sendNotification($recipientId);
    }
}
