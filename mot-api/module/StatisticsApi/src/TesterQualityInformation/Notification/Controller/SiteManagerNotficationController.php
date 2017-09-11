<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service\SiteManagerTqiNotificationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use NotificationApi\Dto\Notification;

class SiteManagerNotificationController extends BaseNotificationController implements AutoWireableInterface
{
    public function __construct(
        SiteManagerTqiNotificationService $service,
        MotAuthorisationServiceInterface $authorisationService
    )
    {
        $this->service = $service;
        $this->authorisationService = $authorisationService;
    }

    public function create($data)
    {
        return parent::create();
    }

    public function checkIfNotificationHasBeenSentAction()
    {
        return $this->checkIfNotificationHasBeenSent(
            PermissionInSystem::NOTIFY_SM_ABOUT_TQI_STATS,
            Notification::TEMPLATE_SM_MONTHLY_NOTIFICATION_ABOUT_TQI);
    }
}
