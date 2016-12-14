<?php

namespace DvsaMotApi\Service\ReplacementCertificate;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Api\Check\CheckResultExceptionTranslator;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\ReplacementCertificateDraft;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Service\MotTestSecurityService;
use Zend\Authentication\AuthenticationService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;

/**
 * Class ReplacementCertificateUpdater
 *
 * @package DvsaMotApi\Service\ReplacementCertificate
 */
class ReplacementCertificateUpdater
{
    private $motTestSecurityService;

    /** @var AuthorisationServiceInterface $authService */
    private $authService;

    /** @var AuthenticationService $motIdentityProvider */
    private $motIdentityProvider;

    /** @var VehicleService */
    private $vehicleService;

    /**
     * ReplacementCertificateUpdater constructor.
     * @param MotTestSecurityService $motTestSecurityService
     * @param AuthorisationServiceInterface $authService
     * @param AuthenticationService $motIdentityProvider
     * @param VehicleService $vehicleService
     */
    public function __construct(
        MotTestSecurityService $motTestSecurityService,
        AuthorisationServiceInterface $authService,
        AuthenticationService $motIdentityProvider,
        VehicleService $vehicleService
    ) {
        $this->motTestSecurityService = $motTestSecurityService;
        $this->authService = $authService;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->vehicleService = $vehicleService;
    }

    /**
     * @param ReplacementCertificateDraft $draft
     * @param bool $dvlaImportProcess
     *
     * @return MotTest
     * @throws ForbiddenException
     */
    public function update(ReplacementCertificateDraft $draft, $dvlaImportProcess = false)
    {
        $hasFullRights = $this->authService->isGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS);

        $motTest = $draft->getMotTest();
        if ($motTest->getVersion() !== $draft->getMotTestVersion()) {
            throw new ForbiddenException(
                "A previous change to certificate has been detected.
                Please try to edit the certificate again"
            );
        }
        if (!$hasFullRights) {
            $checkResult = $this->motTestSecurityService->validateOdometerReadingModificationWindowOpen($motTest);
            CheckResultExceptionTranslator::tryThrowBadRequestException($checkResult);

            if (!$this->motTestSecurityService->isCurrentTesterAssignedToMotTest($motTest)
                && !$draft->getReasonForDifferentTester()
            ) {
                throw new ForbiddenException("Reason for different tester expected");
            }
        } else {
            if (!$draft->getReplacementReason()) {
                throw new ForbiddenException("Reason for replacement expected");
            }
        }

        // Only update vehicle when user initiated from front-end.  Dvla Import has already updated vehicle.
        if ($dvlaImportProcess) {
            $motTest = $this->updateMotTestForDvlaImport($draft, $motTest);
        } else {
            $motTest = $this->updateMotTestForNotDvlaImport($draft, $motTest, $hasFullRights);
        }

        return $motTest;
    }

    /**
     * @param ReplacementCertificateDraft $draft
     * @param MotTest $motTest
     * @return MotTest
     */
    private function updateMotTestForDvlaImport(ReplacementCertificateDraft $draft, MotTest $motTest) {
        $motTest
            ->setVin($draft->getVin())
            ->setRegistration($draft->getVrm());

        $prsTest = $motTest->getPrsMotTest();
        if($prsTest) {
            $motTest->setPrsMotTest(
                $prsTest
                    ->setVin($draft->getVin())
                    ->setRegistration($draft->getVrm())
            );

        }
        return $motTest;
    }


    /** Function for update Test when it is not DVLA Import
     * @param ReplacementCertificateDraft $draft
     * @param MotTest $motTest
     * @param bool $hasFullRights
     * @return MotTest
     */
    private function updateMotTestForNotDvlaImport(
        ReplacementCertificateDraft $draft,
        MotTest $motTest,
        $hasFullRights
    ) {
        $this->updateVehicleFromDraftUsingJavaService($draft, $motTest, $hasFullRights);
        $this->updateMotTestFromDraft($draft, $motTest, $hasFullRights);

        $prsTest = $motTest->getPrsMotTest();
        if($prsTest){
            $motTest->setPrsMotTest(
                $this->updateMotTestFromDraft(
                    $draft,
                    $prsTest,
                    $hasFullRights,
                    true
                )
            );
        }

        return $motTest;
    }

    /**
     * This method's been used to update both MOT-Test and PRS MOT-Test
     *
     * @param ReplacementCertificateDraft $draft
     * @param MotTest $motTest
     * @param bool $hasFullRights
     * @param bool $isPsrTest
     * @return MotTest
     */
    protected function updateMotTestFromDraft(
        ReplacementCertificateDraft $draft,
        MotTest $motTest,
        $hasFullRights,
        $isPsrTest = false
    )
    {
        $motTest->setOdometerReading($draft->getOdometerReading())
            ->setPrimaryColour($draft->getPrimaryColour())
            ->setSecondaryColour($draft->getSecondaryColour());

        if (!$isPsrTest) {
            $motTest->setExpiryDate($draft->getExpiryDate());
        }

        if ($hasFullRights) {
            $motTest
                ->setVehicleTestingStation($draft->getVehicleTestingStation())
                ->setVin($draft->getVin())
                ->setRegistration($draft->getVrm())
                ->setModel($draft->getModelName() ? null : $draft->getModel())
                ->setFreeTextModelName($draft->getModelName())
                ->setMake($draft->getMakeName() ? null : $draft->getMake())
                ->setFreeTextMakeName($draft->getMakeName())
                ->setCountryOfRegistration($draft->getCountryOfRegistration())
                ->setEmptyVinReason(null)
                ->setEmptyVrmReason(null);
        }

        return $motTest;
    }

    /**
     * @param ReplacementCertificateDraft $draft
     * @param MotTest $motTest
     * @param bool $hasFullRights
     */
    private function updateVehicleFromDraftUsingJavaService(ReplacementCertificateDraft $draft, MotTest $motTest, $hasFullRights)
    {
        $updateVehicleRequest = new UpdateDvsaVehicleRequest;

        if ($draft->getPrimaryColour()) {
            $updateVehicleRequest->setColourCode($draft->getPrimaryColour()->getCode());
        }

        if ($draft->getSecondaryColour()) {
            $updateVehicleRequest->setSecondaryColourCode($draft->getSecondaryColour()->getCode());
        }

        if ($hasFullRights) {
            $updateVehicleRequest->setVin($draft->getVin());
            $updateVehicleRequest->setRegistration($draft->getVrm());

            if ($draft->getCountryOfRegistration()) {
                $updateVehicleRequest->setCountryOfRegistrationId($draft->getCountryOfRegistration()->getId());
            }

            if ($draft->getMakeName()) {
                $updateVehicleRequest->setMakeOther($draft->getMakeName());
            } else if ($draft->getMake()) {
                $updateVehicleRequest->setMakeId($draft->getMake()->getId());
            }

            if ($draft->getModelName()) {
                $updateVehicleRequest->setModelOther($draft->getModelName());
            } else if ($draft->getModel()) {
                $updateVehicleRequest->setModelId($draft->getModel()->getId());
            }
        }

        $this->vehicleService->updateDvsaVehicle($motTest->getVehicle()->getId(), $updateVehicleRequest);
    }
}
