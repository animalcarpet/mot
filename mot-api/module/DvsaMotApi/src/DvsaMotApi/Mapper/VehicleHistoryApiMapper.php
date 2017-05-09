<?php

namespace DvsaMotApi\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryDto;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryItemDto;
use DvsaCommon\Utility\AddressUtils;
use DvsaEntities\Entity\MotTest;

class VehicleHistoryApiMapper
{
    /**
     * Maps array of MotTest entity objects to VehicleHistoryDto.
     *
     * @param MotTest[] $motTests
     *
     * @return VehicleHistoryDto
     */
    public function fromArrayOfObjectsToDto(array $motTests)
    {
        $vehicleHistoryDto = new VehicleHistoryDto();

        foreach ($motTests as $motTest) {
            $itemDto = new VehicleHistoryItemDto();
            $itemDto->setId($motTest->getId());
            $itemDto->setStatus($motTest->getStatus());
            $itemDto->setMotTestNumber($motTest->getNumber());
            $itemDto->setTestType($motTest->getMotTestType()->getCode());
            $itemDto->setAllowEdit(false);

            if ($motTest->getPrsMotTest()) {
                $itemDto->setPrsMotTestId($motTest->getPrsMotTest()->getId());
            }

            if ($motTest->getExpiryDate()) {
                $itemDto->setExpiryDate($motTest->getExpiryDate());
            }

            if ($motTest->getIssuedDate()) {
                $itemDto->setIssuedDate(DateTimeApiFormat::dateTime($motTest->getIssuedDate()));
            }

            $site = $motTest->getVehicleTestingStation();

            if ($site) {
                $siteDto = $itemDto->getSite();
                $siteDto->setId($site->getId());
                $siteDto->setName($site->getName());
                $siteDto->setAddress(AddressUtils::stringify($site->getAddress()));
            }

            $vehicleHistoryDto->addItem($itemDto);
        }

        return $vehicleHistoryDto;
    }
}
