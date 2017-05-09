<?php

namespace DvsaMotApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;
use DvsaEntities\Repository\CertificateChangeReasonRepository;

/**
 * Class CertificateChangeService.
 */
class CertificateChangeService
{
    /**
     * @var CertificateChangeReasonRepository
     */
    protected $repository;
    protected $authService;

    /**
     * @param CertificateChangeReasonRepository $repository
     * @param AuthorisationServiceInterface     $authService
     */
    public function __construct(
        CertificateChangeReasonRepository $repository,
        AuthorisationServiceInterface $authService
    ) {
        $this->repository = $repository;
        $this->authService = $authService;
    }

    /**
     * Retrieves all CertChangeDiffTesterReasons.
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getDifferentTesterReasonsAsArray()
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_READ);

        $extractedCertChangeDiffTesterReasons = [];
        $certChangeDiffTesterReasons = $this->repository->findAll();
        foreach ($certChangeDiffTesterReasons as $reason) {
            $extractedCertChangeDiffTesterReasons[] = $this->extractDifferentTesterReason($reason);
        }

        return $extractedCertChangeDiffTesterReasons;
    }

    /**
     * @param CertificateChangeDifferentTesterReason $reason
     *
     * @return array
     */
    private function extractDifferentTesterReason(
        CertificateChangeDifferentTesterReason $reason
    ) {
        return ['code' => $reason->getCode(), 'description' => $reason->getDescription()];
    }
}
