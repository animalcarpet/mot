<?php

namespace DvsaMotApi\Service;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\MotTesting\MotTestOptionsDto;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaEntities\Repository\MotTestRepository;

class MotTestOptionsService
{
    /** @var MotTestRepository $motTestRepository */
    private $motTestRepository;

    /** @var ReadMotTestAssertion $readMotTestAssertion */
    private $readMotTestAssertion;

    public function __construct(
        MotTestRepository $motTestRepository,
        ReadMotTestAssertion $readTestAssertion
    ) {
        $this->motTestRepository = $motTestRepository;
        $this->readMotTestAssertion = $readTestAssertion;
    }

    /**
     * @param string $motTestNumber
     *
     * @return MotTestOptionsDto
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getOptions($motTestNumber)
    {
        $motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);

        $this->readMotTestAssertion->assertGranted($motTest);

        $vehicle = $motTest->getVehicle();
        $id = $vehicle->getId();

        return (new MotTestOptionsDto())
                ->setMotTestStartedDate(DateTimeApiFormat::dateTime($motTest->getStartedDate()))
                ->setVehicleId($vehicle->getId())
                ->setVehicleMake($vehicle->getMakeName())
                ->setVehicleModel($vehicle->getModelName())
                ->setVehicleRegistrationNumber($vehicle->getRegistration())
                ->setMotTestTypeDto(
                    (new MotTestTypeDto())->setId($motTest->getMotTestType()->getId())
                                          ->setCode($motTest->getMotTestType()->getCode())
                );
    }
}
