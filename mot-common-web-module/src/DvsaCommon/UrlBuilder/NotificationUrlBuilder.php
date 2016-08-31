<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Class NotificationUrlBuilder
 *
 * @package DvsaCommon\UrlBuilder
 */
class NotificationUrlBuilder extends UrlBuilder
{
    const NOTIFICATION = 'notification';
    const NOTIFICATION_WITH_ID = 'notification/:id';
    const PERSON = '/person/:personId';
    const READ = '/read';
    const ACTION = '/action';
    const CREATE_NOTIFICATION = "/create";

    protected $routesStructure
        = [
            self::NOTIFICATION_WITH_ID => [
                self::READ   => '',
                self::ACTION => '',
            ],
            self::NOTIFICATION         =>
                [
                    self::PERSON =>
                        [
                            self::READ => '',
                        ],
                    self::CREATE_NOTIFICATION
                ],
        ];

    public static function notification($id)
    {
        $urlBuilder = new self();

        return $urlBuilder->appendRoutesAndParams(self::NOTIFICATION_WITH_ID)->routeParam('id', $id);
    }

    public static function notificationForPerson()
    {
        $urlBuilder = new self();

        return $urlBuilder->appendRoutesAndParams(self::NOTIFICATION)->appendRoutesAndParams(self::PERSON);
    }

    public static function newNotification()
    {
        $urlBuilder = new self();

        return $urlBuilder->appendRoutesAndParams(self::NOTIFICATION);
    }

    public function read()
    {
        return $this->appendRoutesAndParams(self::READ);
    }

    public function action()
    {
        return $this->appendRoutesAndParams(self::ACTION);
    }
}
