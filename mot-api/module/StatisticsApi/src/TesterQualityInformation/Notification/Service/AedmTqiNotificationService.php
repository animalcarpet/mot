<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use NotificationApi\Dto\Notification;

class AedmTqiNotificationService extends TqiNotificationService implements AutoWireableInterface
{
    public function execute()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::NOTIFY_AEDM_AND_AED_ABOUT_TQI_STATS);
        $rows = $this->personRepository->findAllAEDandAEDMS();

        $this->send(
            $rows,
            Notification::TEMPLATE_AED_AND_AEDM_MONTHLY_NOTIFICATION_ABOUT_TQI,
            "/authorised-examiner/%s/test-quality-information"
        );
    }
}
