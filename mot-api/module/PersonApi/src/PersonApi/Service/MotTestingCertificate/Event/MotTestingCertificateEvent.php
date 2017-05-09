<?php

namespace PersonApi\Service\MotTestingCertificate\Event;

use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\QualificationAward;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaEventApi\Service\EventService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Date\DateTimeDisplayFormat;

class MotTestingCertificateEvent implements AutoWireableInterface
{
    private $identityProvider;
    private $eventService;
    private $eventPersonMapRepository;
    private $dateTimeHolder;

    public function __construct(
        MotIdentityProviderInterface $identityProvider,
        EventService $eventService,
        EventPersonMapRepository $eventPersonMapRepository,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->identityProvider = $identityProvider;
        $this->eventService = $eventService;
        $this->eventPersonMapRepository = $eventPersonMapRepository;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function sendCreateEvent(QualificationAward $motTestingCertificate)
    {
        if ($motTestingCertificate->getVehicleClassGroup()->getCode() === VehicleClassGroupCode::BIKES) {
            $eventTypeCode = EventTypeCode::GROUP_A_TESTER_QUALIFICATION;
        } else {
            $eventTypeCode = EventTypeCode::GROUP_B_TESTER_QUALIFICATION;
        }

        $this->send(
            EventDescription::MOT_TESTING_QUALIFICATION_CERTIFICATE_RECORD,
            $eventTypeCode,
            $motTestingCertificate
        );
    }

    public function sendUpdateEvent(QualificationAward $motTestingCertificate)
    {
        if ($motTestingCertificate->getVehicleClassGroup()->getCode() === VehicleClassGroupCode::BIKES) {
            $eventTypeCode = EventTypeCode::GROUP_A_TESTER_QUALIFICATION;
        } else {
            $eventTypeCode = EventTypeCode::GROUP_B_TESTER_QUALIFICATION;
        }

        $this->send(
            EventDescription::MOT_TESTING_QUALIFICATION_CERTIFICATE_EDIT,
            $eventTypeCode,
            $motTestingCertificate
        );
    }

    public function sendRemoveEvent(QualificationAward $motTestingCertificate)
    {
        if ($motTestingCertificate->getVehicleClassGroup()->getCode() === VehicleClassGroupCode::BIKES) {
            $eventTypeCode = EventTypeCode::REMOVAL_OF_GROUP_A_CERTIFICATE;
        } else {
            $eventTypeCode = EventTypeCode::REMOVAL_OF_GROUP_B_CERTIFICATE;
        }

        $this->send(
            EventDescription::MOT_TESTING_QUALIFICATION_CERTIFICATE_REMOVE,
            $eventTypeCode,
            $motTestingCertificate
        );
    }

    private function send($eventDescription, $eventCode, QualificationAward $motTestingCertificate)
    {
        $description = sprintf(
            $eventDescription,
            $motTestingCertificate->getVehicleClassGroup()->getCode(),
            $this->identityProvider->getIdentity()->getUsername(),
            $motTestingCertificate->getCertificateNumber(),
            DateTimeDisplayFormat::date($motTestingCertificate->getDateOfQualification())
        );

        $event = $this
            ->eventService
            ->addEvent(
                $eventCode,
                $description,
                $this->dateTimeHolder->getCurrent(true)
            );

        $eventPersonMap = new EventPersonMap();
        $eventPersonMap
            ->setPerson($motTestingCertificate->getPerson())
            ->setEvent($event);

        $this->eventPersonMapRepository->save($eventPersonMap);
    }
}
