<?php

namespace PersonApi\Service\Mapper;

use DvsaCommon\Mapper\TesterGroupAuthorisationMapperInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

class TesterGroupAuthorisationMapper implements TesterGroupAuthorisationMapperInterface, AutoWireableInterface
{
    const ERR_MSG_DIFFERENT_CLASSES_IN_GROUP = 'All classes in the group %s should have same status';
    const ERR_MSG_UNEXPECTED_CLASSES = 'Unexpected Vehicle class (%s) spotted';

    private $authForMotTestingService;

    public function __construct(PersonalAuthorisationForMotTestingService $authForMotTestingService)
    {
        $this->authForMotTestingService = $authForMotTestingService;
    }

    public function getAuthorisation($personId)
    {
        $collector = $this->authForMotTestingService->getPersonalTestingAuthorisation($personId);
        $vehicleClassAuthorisations = $this->cleanResponse($collector->toArray());

        return $this->groupAuthorisations($vehicleClassAuthorisations);
    }

    private function cleanResponse(array $vehicleClassAuthorisations)
    {
        return ArrayUtils::mapWithKeys($vehicleClassAuthorisations,
            function ($key, $value) {
                return substr($key, 5);
            },
            function ($key, $value) {
                return $value;
            }
        );
    }

    private function groupAuthorisations(array $vehicleClassAuthorisations)
    {
        $groupedQualification = [
            VehicleClassGroupCode::BIKES => [],
            VehicleClassGroupCode::CARS_ETC => [],
        ];

        foreach ($vehicleClassAuthorisations as $class => $status) {
            $groupedQualification[$this->getClassGroup($class)][] = $status;
        }

        $groupAuthorisations = $this->validateAndSquashGroupStatus($groupedQualification);

        $statusACode = $groupAuthorisations[VehicleClassGroupCode::BIKES];
        $statusBCode = $groupAuthorisations[VehicleClassGroupCode::CARS_ETC];

        $statusA = new TesterGroupAuthorisationStatus(
            $groupAuthorisations[VehicleClassGroupCode::BIKES],
            $statusACode ? $this->getNameForStatus($statusACode) : self::DEFAULT_NO_STATUS
        );

        $statusB = new TesterGroupAuthorisationStatus(
            $groupAuthorisations[VehicleClassGroupCode::CARS_ETC],
            $statusBCode ? $this->getNameForStatus($statusBCode) : self::DEFAULT_NO_STATUS
        );

        return new TesterAuthorisation($statusA, $statusB);
    }

    private function getClassGroup($vehicleClass)
    {
        $group = '';

        if (VehicleClassGroup::isGroupA($vehicleClass)) {
            $group = VehicleClassGroupCode::BIKES;
        } elseif (VehicleClassGroup::isGroupB($vehicleClass)) {
            $group = VehicleClassGroupCode::CARS_ETC;
        }

        if (VehicleClassGroupCode::BIKES !== $group && VehicleClassGroupCode::CARS_ETC !== $group) {
            throw new \UnexpectedValueException(sprintf(self::ERR_MSG_UNEXPECTED_CLASSES, $vehicleClass));
        }

        return $group;
    }

    private function validateAndSquashGroupStatus($groupQualification)
    {
        $squashed = [];

        foreach ($groupQualification as $group => $statuses) {
            $avc = array_unique($statuses);
            $numberOfDifferentStatus = count($avc);

            if (1 !== $numberOfDifferentStatus) {
                throw new \DomainException(sprintf(self::ERR_MSG_DIFFERENT_CLASSES_IN_GROUP, $group));
            }

            $squashed[$group] = $statuses[0];
        }

        return $squashed;
    }

    private function getNameForStatus($status)
    {
        if (!AuthorisationForTestingMotStatusCode::exists($status)) {
            throw new \InvalidArgumentException('Unknown status named: '.$status);
        }

        return $status;
    }
}
