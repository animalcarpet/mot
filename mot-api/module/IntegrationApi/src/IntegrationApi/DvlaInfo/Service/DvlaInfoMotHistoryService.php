<?php

namespace IntegrationApi\DvlaInfo\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaEntities\Repository\MotTestRepository;
use IntegrationApi\DvlaInfo\Mapper\DvlaInfoMotHistoryMapper;
use ZendPdf\Exception\NotImplementedException;

class DvlaInfoMotHistoryService
{

    private $motTestRepository;
    private $mapper;

    public function __construct(MotTestRepository $motTestRepository)
    {
        $this->motTestRepository = $motTestRepository;
        $this->mapper = new DvlaInfoMotHistoryMapper();
    }

    /**
     * Returns a list of passed and failed MOT Tests.
     * Tests are associated with vehicle's vrm and one of its test number or v5c reference (whichever is given).
     *
     * @param $vrm          String
     * @param $testNumber   String
     * @param $v5cReference String
     *
     * @return array
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getMotTests($vrm, $testNumber, $v5cReference)
    {
        if ($testNumber) {
            return $this->getMotTestsForVehicleAssociatedWithTestNumber($vrm, $testNumber);
        } else {
            return $this->getMotTestsForVehicleAssociatedWithV5cReference($vrm, $v5cReference);
        }
    }

    private function getMotTestsForVehicleAssociatedWithTestNumber($vrm, $testNumber)
    {
        try {
            $motTest = $this->motTestRepository->findTestByVehicleRegistrationAndTestNumber($vrm, $testNumber);
        } catch (NoResultException $exception) {
            throw new NotFoundException("MOT tests");
        }

        $motTests = $this->motTestRepository->findTestsExcludingNonAuthoritativeTestsForVehicle($motTest->getVehicle()->getId(), null);

        return $this->mapper->toArray($motTests);
    }

    private function getMotTestsForVehicleAssociatedWithV5cReference($vrm, $v5cReference)
    {
        $exception = new ServiceException(null);
        $exception->addError(
            "TODO(PT): To be implemented when a placement of v5c is known.", ServiceException::DEFAULT_STATUS_CODE
        );
        throw $exception;
    }
}
