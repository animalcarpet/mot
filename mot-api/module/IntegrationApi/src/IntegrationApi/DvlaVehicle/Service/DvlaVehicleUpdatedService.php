<?php

namespace IntegrationApi\DvlaVehicle\Service;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateService;

/**
 * Class DvlaVehicleUpdatedService
 */
class DvlaVehicleUpdatedService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    const CHERISHED_TRANSFER_REASON = 'DVLA Cherished Transfer';

    /**
     * @var MotTestRepository
     */
    private $motTestRepository;

    /**
     * @var ReplacementCertificateService
     */
    private $replacementCertificateService;

    /**
     * @var VehicleService
     */
    private $vehicleService;

    /**
     * @param MotTestRepository $motTestRepository
     * @param ReplacementCertificateService $replacementCertificateService
     * @param VehicleService $vehicleService
     */
    public function __construct(
        MotTestRepository $motTestRepository,
        ReplacementCertificateService $replacementCertificateService,
        VehicleService $vehicleService
    ) {
        $this->motTestRepository = $motTestRepository;
        $this->replacementCertificateService = $replacementCertificateService;
        $this->vehicleService = $vehicleService;
    }

    /**
     * Get the latest passed MOT test number for a vehicle ID
     * @param int $vehicleId
     * @param int $userId
     * @return bool
     */
    public function createReplacementCertificate($vehicleId, $userId)
    {
        $latestMotTestNumber = $this->motTestRepository->getLatestMotTestIdByVehicleId($vehicleId);

        $vehicle = $this->vehicleService->getDvsaVehicleById($vehicleId);
        $changeDto = new ReplacementCertificateDraftChangeDTO();
        $changeDto->setVrm($vehicle->getRegistration());
        $changeDto->setVin($vehicle->getVin());
        $changeDto->setIsDvlaImportProcess(true);

        return $this->inTransaction(
            function () use ($latestMotTestNumber, $userId, $changeDto) {
                $reason = self::CHERISHED_TRANSFER_REASON;
                $draftId = $this->replacementCertificateService->createAndUpdateDraft(
                    $latestMotTestNumber, $reason, $changeDto
                )->getId();
                $this->replacementCertificateService->applyDraft($draftId, [], true);
                $this->replacementCertificateService->createCertificate($latestMotTestNumber, $userId);
            }
        );
    }
}
