<?php

namespace DvsaMotApi\Service;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\Time;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;

/**
 * Service that notifies interested parties related to the site about a test performed outside site opening hours.
 */
class TestingOutsideOpeningHoursNotificationService
{
    /** @var NotificationService */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function notify(Site $site, Person $tester, \DateTime $completionDateTime, Person $recipient)
    {
        $siteAddress = self::resolveSiteAddress($site);
        $dateToDisplay = DateTimeDisplayFormat::date($completionDateTime);
        $timeToDisplay = DateTimeDisplayFormat::time($completionDateTime);

        $notification = (new Notification())->setTemplate(Notification::TEMPLATE_TESTING_OUTSIDE_OPENING_HOURS)
            ->setRecipient($recipient->getId())
            ->addField('username', $tester->getUsername())
            ->addField('time', $timeToDisplay)
            ->addField('date', $dateToDisplay)
            ->addField('siteNumber', $site->getSiteNumber())
            ->addField('address', $siteAddress)
            ->toArray();

        $this->notificationService->add($notification);
    }

    private static function resolveSiteAddress(Site $site)
    {
        $emptyStrOrNullFilter = function ($e) {
            return !(is_null($e) || (is_string($e) && empty($e)));
        };
        $contact = $site->getBusinessContact() ?: $site->getCorrespondenceContact();
        $address = $contact->getDetails()->getAddress();
        $addressLinesAsArray = [
            $address->getAddressLine1(),
            $address->getAddressLine2(),
            $address->getAddressLine3(),
            $address->getAddressLine4(),
        ];
        $filteredEmptyLines = ArrayUtils::filter($addressLinesAsArray, $emptyStrOrNullFilter);

        return implode(', ', $filteredEmptyLines);
    }
}
