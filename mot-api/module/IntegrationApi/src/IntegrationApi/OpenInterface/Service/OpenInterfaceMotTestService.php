<?php

namespace IntegrationApi\OpenInterface\Service;

use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaEntities\Entity\DvlaVehicle;
use IntegrationApi\OpenInterface\Mapper\OpenInterfaceMotTestMapper;
use IntegrationApi\OpenInterface\Repository\OpenInterfaceMotTestRepository;
use IntegrationApi\OpenInterface\Validator\OpenInterfaceMotTestRequestValidator;

/**
 * Class OpenInterfaceMotTestService.
 */
class OpenInterfaceMotTestService
{
    private $repository;
    private $mapper;

    const PRE_1960_DATE = 1960;

    /**
     * @param OpenInterfaceMotTestRepository $repository
     */
    public function __construct(OpenInterfaceMotTestRepository $repository)
    {
        $this->repository = $repository;
        $this->mapper = new OpenInterfaceMotTestMapper();
    }

    /**
     * Returns data of a Passed test with latest in the future expiry date.
     * Test was issued before or on given day (today if no date specified).
     * Throws 404 if only Failed tests found or no tests found at all.
     *
     * @param $vrm - vehicle registration mark
     * @param $issuedBeforeString - latest possible date of the issue given in YYYYMMDD format
     *
     * @return array - MOT Test data
     *
     * @throws \DvsaCommonApi\Service\Exception\ServiceException  when Pass MOT test not found
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException when none (Pass or Fail MOT) test found at all
     */
    public function getPassMotTestForVehicleIssuedBefore($vrm, $issuedBeforeString = null)
    {
        if (is_null($issuedBeforeString)) {
            $issuedBefore = new \DateTime();
        } else {
            $issuedBefore = $this->toDateTimeObject($issuedBeforeString);
        }

        $passedMotTest = $this->repository->findLatestMotTestForVrm(
            $vrm, $issuedBefore, MotTestStatusName::PASSED, [
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
                MotTestTypeCode::TARGETED_REINSPECTION,
                MotTestTypeCode::MOT_COMPLIANCE_SURVEY,
                MotTestTypeCode::NON_MOT_TEST,
                MotTestTypeCode::MYSTERY_SHOPPER,
            ]
        );

        if ($passedMotTest === null || $passedMotTest->isExpired()) {
            $vehicle = $this->repository->findVehicleByVrm($vrm);

            $primaryColour = null;
            $secondaryColour = null;
            $dvlaMakeName = null;
            $dvlaModelName = null;

            if (null !== $vehicle && $this->isVehiclePre1960($vehicle)) {
                $colourCode = $vehicle->getPrimaryColour();
                if ($colourCode !== null) {
                    $colour = $this->repository->findColourByCode($colourCode);
                    if ($colour !== null) {
                        $primaryColour = $colour->GetName();
                    }
                }

                $colourCode = $vehicle->getSecondaryColour();
                if ($colourCode !== null) {
                    $colour = $this->repository->findColourByCode($colourCode);
                    if ($colour !== null) {
                        $secondaryColour = $colour->GetName();
                    }
                }

                $dvlaMakeCode = $vehicle->getMakeCode();
                if ($dvlaMakeCode !== null) {
                    $dvlaMake = $this->repository->findDvlaMakeByCode($dvlaMakeCode);
                    if ($dvlaMake !== null) {
                        $dvlaMakeName = $dvlaMake->GetName();
                    }

                    $dvlaModelCode = $vehicle->getModelCode();
                    if ($dvlaModelCode !== null) {
                        $dvlaModel = $this->repository->findDvlaModelByMakeCodeModelCode($dvlaMakeCode, $dvlaModelCode);
                        if ($dvlaModel !== null) {
                            $dvlaModelName = $dvlaModel->GetName();
                        }
                    }
                }

                return $this->mapper->pre1960VehicleWithNoMotTestToArray(
                    $vehicle,
                    $primaryColour,
                    $secondaryColour,
                    $dvlaMakeName,
                    $dvlaModelName
                );
            }
        }

        if ($passedMotTest) {
            return $this->mapper->toArray($passedMotTest);
        }

        $failedMotTest = $this->repository->findLatestMotTestForVrm(
            $vrm, $issuedBefore, MotTestStatusName::FAILED, [
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
                MotTestTypeCode::TARGETED_REINSPECTION,
                MotTestTypeCode::MOT_COMPLIANCE_SURVEY,
                MotTestTypeCode::NON_MOT_TEST,
                MotTestTypeCode::MYSTERY_SHOPPER,
            ]
        );

        if ($failedMotTest) {
            $noPassException = new ServiceException(null, NotFoundException::ERROR_CODE_NOT_FOUND);
            $noPassException->addError('Pass MOT test not found; Fail found', 'NO_PASS', null);

            throw $noPassException;
        }

        throw new NotFoundException('MOT test');
    }

    /**
     * @param DvlaVehicle $vehicle
     *
     * @return bool
     */
    private function isVehiclePre1960(DvlaVehicle $vehicle)
    {
        return ($vehicle->getManufactureDate() && $vehicle->getManufactureDate()->format('Y') < self::PRE_1960_DATE)
        || ($vehicle->getFirstRegistrationDate() && $vehicle->getFirstRegistrationDate()->format('Y') < self::PRE_1960_DATE);
    }

    private function toDateTimeObject($dateString)
    {
        $validator = new OpenInterfaceMotTestRequestValidator();
        $validator->validateDate($dateString);

        $toDayEnd = $dateString.' 235959';

        return \DateTime::createFromFormat('Ymd His', $toDayEnd);
    }
}
