<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service;


use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Formatting\AddressFormatter;
use DvsaEntities\Entity\Address;
use DvsaEntities\Mapper\AddressMapper;
use NotificationApi\Dto\Notification;

class SiteManagerTqiNotificationService extends TqiNotificationService implements AutoWireableInterface
{
    public function execute()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::NOTIFY_SM_ABOUT_TQI_STATS);
        $notifications = $this->personRepository->findAllSiteManagers();
        $addressMapper = new AddressMapper();
        $addressFormatter = new AddressFormatter();

        foreach ($notifications as $key => $notification) {
            /** @var Address $address */
            $address = $addressMapper->mapToEntity(new Address(), $notification);
            $notification['address'] = $addressFormatter->format($address);
            $notifications[$key] = $this->unsetUnusedParameters($notification);
        }

        parent::send(
            $notifications,
            Notification::TEMPLATE_SM_MONTHLY_NOTIFICATION_ABOUT_TQI,
            "/vehicle-testing-station/%s/test-quality"
        );
    }

    private function unsetUnusedParameters(array $data)
    {
        unset($data['addressLine1']);
        unset($data['addressLine2']);
        unset($data['addressLine3']);
        unset($data['addressLine4']);
        unset($data['town']);
        unset($data['country']);
        unset($data['postcode']);

        return $data;
    }
}