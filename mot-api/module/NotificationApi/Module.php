<?php

namespace NotificationApi;

use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use NotificationApi\Factory\Service\UserOrganisationNotificationServiceFactory;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use NotificationApi\Service\Helper\SiteNominationEventHelper;
use NotificationApi\Factory\Helper\SiteNominationEventHelperFactory;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;
use NotificationApi\Factory\Helper\OrganisationNominationEventHelperFactory;
use NotificationApi\Factory\Service\PositionRemovalNotificationServiceFactory;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\Helper\NotificationTradeTemplateHelper;
use NotificationApi\Factory\Service\Helper\NotificationTradeTemplateHelperFactory;

/**
 * Class Module
 *
 * @package NotificationApi
 */
class Module
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories'  => [
                NotificationService::class => \NotificationApi\Factory\NotificationServiceFactory::class,
                SiteNominationEventHelper::class => SiteNominationEventHelperFactory::class,
                OrganisationNominationEventHelper::class => OrganisationNominationEventHelperFactory::class,
                PositionRemovalNotificationService::class => PositionRemovalNotificationServiceFactory::class,
                UserOrganisationNotificationService::class => UserOrganisationNotificationServiceFactory::class,
                NotificationTradeTemplateHelper::class => NotificationTradeTemplateHelperFactory::class,
            ],
            'abstract_factories' => [
                AutoWireFactory::class,
            ],
            'invokables' => [

            ]
        ];
    }
}
