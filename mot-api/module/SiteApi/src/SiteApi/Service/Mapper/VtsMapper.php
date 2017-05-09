<?php

namespace SiteApi\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\Time;
use DvsaCommon\Dto\BrakeTest\BrakeTestTypeDto;
use DvsaCommon\Dto\Security\RoleDto;
use DvsaCommon\Dto\Security\RolesMapDto;
use DvsaCommon\Dto\Security\RoleStatusDto;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\SiteTestingDailyScheduleDto;
use DvsaEntities\Entity;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\SiteBusinessRoleMap;

class VtsMapper extends SiteMapper
{
    /**
     * @param Entity\Site $vts
     *
     * @return VehicleTestingStationDto
     */
    public function toDto($vts)
    {
        $vtsDto = new VehicleTestingStationDto();

        parent::toDto($vts, $vtsDto);

        $vtsDto
            ->setTestClasses($this->getTestClasses($vts))
            ->setFacilities($this->mapFacilities($vts))

            ->setDefaultBrakeTestClass1And2($this->mapBrakeTestTypeDto($vts->getDefaultBrakeTestClass1And2()))
            ->setDefaultParkingBrakeTestClass3AndAbove(
                $this->mapBrakeTestTypeDto($vts->getDefaultParkingBrakeTestClass3AndAbove())
            )

            ->setDefaultServiceBrakeTestClass3AndAbove(
                $this->mapBrakeTestTypeDto($vts->getDefaultServiceBrakeTestClass3AndAbove())
            )

            ->setPositions($this->mapPositions($vts))
            //  not implemented yet ->setEquipments()
            //  not implemented yet ->setMotTests()
            ->setSiteTestingSchedule($this->mapSchedule($vts));

        return $vtsDto;
    }

    /**
     * @param BrakeTestType $typeData
     *
     * @return BrakeTestTypeDto|null
     */
    private function mapBrakeTestTypeDto($typeData)
    {
        if (!($typeData instanceof BrakeTestType)) {
            return null;
        }

        return (new  BrakeTestTypeDto())
            ->setId($typeData->getId())
            ->setCode($typeData->getCode())
            ->setName($typeData->getName());
    }

    /**
     * @param Entity\Site $vts
     *
     * @return string[]
     */
    private function getTestClasses($vts)
    {
        $testClasses = [];

        /** @var AuthorisationForTestingMotAtSite $obj */
        foreach ($vts->getAuthorisationForTestingMotAtSite() as $obj) {
            $testClasses[] = $obj->getVehicleClass()->getCode();
        }

        return $testClasses;
    }

    /**
     * @param Entity\Site $vts
     *
     * @return FacilityDto[]
     */
    private function mapFacilities($vts)
    {
        if (empty($vts->getFacilities())) {
            return null;
        }

        $facilities = [];

        foreach ($vts->getFacilities() as $facility) {
            $typeEntity = $facility->getFacilityType();
            $typeCode = $typeEntity->getCode();

            if (!isset($facilities[$typeCode])) {
                $facilities[$typeCode] = [];
            }

            //  --  facility type   --
            $typeDto = new FacilityTypeDto();
            $typeDto
                ->setId($typeEntity->getId())
                ->setCode($typeCode)
                ->setName($typeEntity->getName());

            //  --  facility --
            $dto = new FacilityDto();
            $dto
                ->setId($facility->getId())
                ->setName($facility->getName())
                ->setType($typeDto);

            $facilities[$typeCode][] = $dto;
        }

        return $facilities;
    }

    /**
     * @param Entity\Site $vts
     *
     * @return SiteTestingDailyScheduleDto[]
     */
    private function mapSchedule($vts)
    {
        $entities = $vts->getSiteTestingSchedule();
        if (empty($entities)) {
            return null;
        }

        $dtos = [];

        /** @var Entity\SiteTestingDailySchedule $entity */
        foreach ($entities as $entity) {
            if ($entity instanceof Entity\SiteTestingDailySchedule) {
                $dto = (new SiteTestingDailyScheduleDto())
                    ->setWeekday($entity->getWeekday());

                $time = $entity->getOpenTime();
                if ($time instanceof Time) {
                    $dto->setOpenTime($time->toIso8601());
                }

                $time = $entity->getCloseTime();
                if ($time instanceof Time) {
                    $dto->setCloseTime($time->toIso8601());
                }

                $dtos[] = $dto;
            }
        }

        return $dtos;
    }

    /**
     * @param Entity\Site $vts
     *
     * @return RolesMapDto[]
     */
    private function mapPositions($vts)
    {
        $entities = $vts->getPositions();
        if (empty($entities)) {
            return null;
        }

        $dtos = [];

        /** @var SiteBusinessRoleMap $entity */
        foreach ($entities as $entity) {
            //  --  role --
            $roleEntity = $entity->getSiteBusinessRole();
            $roleDto = new RoleDto();
            $roleDto
                ->setId($roleEntity->getId())
                ->setCode($roleEntity->getCode())
                ->setName($roleEntity->getName());

            //  --  role status --
            $roleStatusEntity = $entity->getBusinessRoleStatus();
            $roleStatusDto = new RoleStatusDto();
            $roleStatusDto
                ->setId($roleStatusEntity->getId())
                ->setCode($roleStatusEntity->getCode())
                ->setName($roleStatusEntity->getName());

            //  --  map --
            $dto = new RolesMapDto();
            $dto->setId($entity->getId())
                ->setValidFrom(DateTimeApiFormat::dateTime($entity->getValidFrom()))
                ->setExpiryDate(DateTimeApiFormat::dateTime($entity->getExpiryDate()))
                ->setStatusChangedOn(DateTimeApiFormat::dateTime($entity->getStatusChangedOn()))
                ->setPerson(
                    $this->personMapper->toDto($entity->getPerson())
                )
                ->setRole($roleDto)
                ->setRoleStatus($roleStatusDto);

            $dtos[] = $dto;
        }

        return $dtos;
    }
}
