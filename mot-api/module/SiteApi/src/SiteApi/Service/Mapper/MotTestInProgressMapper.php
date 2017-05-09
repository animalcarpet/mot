<?php

namespace SiteApi\Service\Mapper;

use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;
use DvsaEntities\Entity\EmptyVrmReason;
use DvsaEntities\Entity\MotTest;

/**
 * Class MotTestInProgressMapper.
 */
class MotTestInProgressMapper
{
    /**
     * @param MotTest[] $motTests
     *
     * @return MotTestInProgressDto[]
     */
    public function manyToDto($motTests)
    {
        $data = [];

        foreach ($motTests as $motTest) {
            $data[] = $this->toDto($motTest);
        }

        return $data;
    }

    /**
     * @param MotTest $motTest
     *
     * @return MotTestInProgressDto
     */
    public function toDto(MotTest $motTest)
    {
        $testDto = new MotTestInProgressDto();
        $testDto->setMotTestId($motTest->getId());
        $testDto->setVehicleRegisteredNumber($motTest->getRegistration())
            ->setTesterName($motTest->getTester()->getDisplayName())
            ->setNumber($motTest->getNumber())
            ->setVehicleModel($motTest->getModelName())
            ->setVehicleMake($motTest->getMakeName());

        if ($motTest->getEmptyVrmReason() instanceof EmptyVrmReason) {
            $testDto->setEmptyVrmReasonName($motTest->getEmptyVrmReason()->getName());
        }

        return $testDto;
    }
}
