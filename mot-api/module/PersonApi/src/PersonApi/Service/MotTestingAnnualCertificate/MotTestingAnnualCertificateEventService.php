<?php
namespace PersonApi\Service\MotTestingAnnualCertificate;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\QualificationAnnualCertificate;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaEventApi\Service\EventService;

class MotTestingAnnualCertificateEventService implements AutoWireableInterface
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

    public function sendCreateEvent(QualificationAnnualCertificate $certificate)
    {
        switch ($certificate->getVehicleClassGroup()->getCode()) {
            case VehicleClassGroupCode::BIKES:
                $eventTypeCode = EventTypeCode::GROUP_A_TESTER_ANNUAL_EXAM;
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $eventTypeCode = EventTypeCode::GROUP_B_TESTER_ANNUAL_EXAM;
                break;
            default:
                throw new \InvalidArgumentException("Wrong group code");
        }

        $description = sprintf(
            EventDescription::MOT_TESTING_ANNUAL_CERTIFICATE_RECORD,
            $certificate->getVehicleClassGroup()->getCode(),
            $this->identityProvider->getIdentity()->getUsername(),
            $certificate->getCertificateNumber(),
            DateTimeDisplayFormat::date($certificate->getDateAwarded())
        );

        $this->send($description, $eventTypeCode, $certificate);
    }

    public function sendUpdateEvent(
        QualificationAnnualCertificate $oldCertificate,
        QualificationAnnualCertificate $certificate
    ) {
        switch ($certificate->getVehicleClassGroup()->getCode()) {
            case VehicleClassGroupCode::BIKES:
                $eventTypeCode = EventTypeCode::CHANGE_GROUP_A_TESTER_ANNUAL_EXAM;
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $eventTypeCode = EventTypeCode::CHANGE_GROUP_B_TESTER_ANNUAL_EXAM;
                break;
            default:
                throw new \InvalidArgumentException("Wrong group code");
        }

        $description = sprintf(
            EventDescription::MOT_TESTING_ANNUAL_CERTIFICATE_EDIT,
            $certificate->getVehicleClassGroup()->getCode(),
            $this->identityProvider->getIdentity()->getUsername(),
            $oldCertificate->getCertificateNumber(),
            $certificate->getCertificateNumber(),
            DateTimeDisplayFormat::date($oldCertificate->getDateAwarded()),
            DateTimeDisplayFormat::date($certificate->getDateAwarded()),
            $oldCertificate->getScore(),
            $certificate->getScore()
        );

        $this->send($description, $eventTypeCode, $certificate);
    }

    public function sendRemoveEvent(QualificationAnnualCertificate $certificate)
    {
        switch ($certificate->getVehicleClassGroup()->getCode()) {
            case VehicleClassGroupCode::BIKES:
                $eventTypeCode = EventTypeCode::REMOVE_GROUP_A_TESTER_ANNUAL_EXAM;
                break;
            case VehicleClassGroupCode::CARS_ETC:
                $eventTypeCode = EventTypeCode::REMOVE_GROUP_B_TESTER_ANNUAL_EXAM;
                break;
            default:
                throw new \InvalidArgumentException("Wrong group code");
        }

        $description = sprintf(EventDescription::MOT_TESTING_ANNUAL_CERTIFICATE_REMOVE,
            $certificate->getVehicleClassGroup()->getCode(),
            $this->identityProvider->getIdentity()->getUsername(),
            $certificate->getCertificateNumber(),
            DateTimeDisplayFormat::date($certificate->getDateAwarded()),
            $certificate->getScore()
        );

        $this->send($description, $eventTypeCode, $certificate);
    }

    private function send($description, $eventCode, QualificationAnnualCertificate $certificate)
    {
        $event = $this
            ->eventService
            ->addEvent(
                $eventCode,
                $description,
                $this->dateTimeHolder->getCurrent(true)
            );

        $eventPersonMap = new EventPersonMap();
        $eventPersonMap
            ->setPerson($certificate->getPerson())
            ->setEvent($event);

        $this->eventPersonMapRepository->save($eventPersonMap);
    }
}