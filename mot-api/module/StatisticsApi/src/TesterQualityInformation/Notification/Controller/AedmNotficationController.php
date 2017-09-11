<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Controller;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service\AedmTqiNotificationService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use NotificationApi\Dto\Notification;

class AedmNotificationController extends BaseNotificationController implements AutoWireableInterface
{
    public function __construct(
        AedmTqiNotificationService $service,
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
            PermissionInSystem::NOTIFY_AEDM_AND_AED_ABOUT_TQI_STATS,
            Notification::TEMPLATE_AED_AND_AEDM_MONTHLY_NOTIFICATION_ABOUT_TQI);
    }
}
