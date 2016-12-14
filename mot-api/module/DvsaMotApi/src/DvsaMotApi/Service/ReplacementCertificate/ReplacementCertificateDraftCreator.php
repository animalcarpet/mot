<?php

namespace DvsaMotApi\Service\ReplacementCertificate;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\ReplacementCertificateDraft;
use DvsaMotApi\Service\MotTestSecurityService;

/**
 * Class ReplacementCertificateDraftCreator
 *
 * @package DvsaMotApi\Service\ReplacementCertificate
 */
class ReplacementCertificateDraftCreator
{

    /**
     * @var \DvsaAuthorisation\Service\AuthorisationServiceInterface $authorizationService
     */
    private $authorizationService;
    /**
     * @var \DvsaMotApi\Service\MotTestSecurityService $motTestSecurityService
     */
    private $motTestSecurityService;

    /**
     * @param MotTestSecurityService                $motTestSecurityService
     * @param AuthorisationServiceInterface                  $authorizationService
     */
    public function __construct(
        MotTestSecurityService $motTestSecurityService,
        AuthorisationServiceInterface $authorizationService
    ) {
        $this->motTestSecurityService = $motTestSecurityService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param MotTest $motTest
     *
     * @return ReplacementCertificateDraft
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function create(MotTest $motTest, $replacementReason = '')
    {
        $hasFullRights = $this->authorizationService->isGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS);

        if (!$motTest->isPassedOrFailed()) {
            throw new ForbiddenException("Mot test is neither PASSED nor FAILED");
        }

        $vtsId = $motTest->getVehicleTestingStation()->getId();
        // to be reviewed: implicit assumption: !ADMIN => TESTER
        if (!$hasFullRights && !$this->motTestSecurityService->isCurrentTesterAssignedToVts($vtsId)) {
            throw new ForbiddenException(
                "Current user is not allowed to replace this certificate since he is not registered
                 in the VTS the certificate was issued at"
            );
        }

        $makeName = $motTest->getMake() ? null : $motTest->getMakeName();
        $modelName = $motTest->getModel() ? null : $motTest->getModelName();

        $draft = ReplacementCertificateDraft::create()
            ->setMotTest($motTest)
            ->setMotTestVersion($motTest->getVersion())
            ->setOdometerReading($motTest->getOdometerReading())
            ->setPrimaryColour($motTest->getPrimaryColour())
            ->setSecondaryColour($motTest->getSecondaryColour())
            ->setExpiryDate($motTest->getExpiryDate())
            ->setVrm($motTest->getRegistration())
            ->setEmptyVrmReason($motTest->getEmptyVrmReason())
            ->setVin($motTest->getVin())
            ->setEmptyVinReason($motTest->getEmptyVinReason())
            ->setMake($motTest->getMake())
            ->setMakeName($makeName)
            ->setModel($motTest->getModel())
            ->setModelName($modelName)
            ->setCountryOfRegistration($motTest->getCountryOfRegistration())
            ->setExpiryDate($motTest->getExpiryDate())
            ->setVehicleTestingStation($motTest->getVehicleTestingStation())
            ->setReplacementReason($replacementReason)
            ->setIsVinVrmExpiryChanged(false);

        return $draft;
    }
}
