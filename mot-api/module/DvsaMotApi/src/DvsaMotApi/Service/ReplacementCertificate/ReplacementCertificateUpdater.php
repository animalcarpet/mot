<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service\ReplacementCertificate;

use Api\Check\CheckResultExceptionTranslator;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestSecurityService;
use Zend\Authentication\AuthenticationService;

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

    /** @var  MotTestRepository */
    private $motTestRepository;

    /**
     * ReplacementCertificateUpdater constructor.
     * @param MotTestSecurityService $motTestSecurityService
     * @param AuthorisationServiceInterface $authService
     * @param AuthenticationService $motIdentityProvider
     * @param VehicleService $vehicleService
     * @param MotTestRepository $motTestRepository
     */
    public function __construct(
        MotTestSecurityService $motTestSecurityService,
        AuthorisationServiceInterface $authService,
        AuthenticationService $motIdentityProvider,
        VehicleService $vehicleService,
        MotTestRepository $motTestRepository
    )
    {
        $this->motTestSecurityService = $motTestSecurityService;
        $this->authService = $authService;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->vehicleService = $vehicleService;
        $this->motTestRepository = $motTestRepository;
    }

    /**
     * @param CertificateReplacementDraft $draft
     * @param bool $isDvlaImport
     * @return MotTest
     * @throws ForbiddenException
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function update(CertificateReplacementDraft $draft, $isDvlaImport = false)
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
                && !$draft->getDifferentTesterReason()
            ) {
                throw new ForbiddenException("Reason for different tester expected");
            }
        } else {
            if (!$draft->getReasonForReplacement()) {
                throw new ForbiddenException("Reason for replacement expected");
            }
        }

        $this->updateMotTestFromDraft($draft, $motTest, $hasFullRights);

        if($isDvlaImport) {
            $vehicle = $this->vehicleService->getDvsaVehicleById($motTest->getVehicle()->getId());
        } else {
            $vehicle = $this->updateVehicleFromDraftUsingJavaService($draft, $motTest, $hasFullRights);
        }

        $motTest->setVehicleVersion($vehicle->getVersion());

        $prsTest = $motTest->getPrsMotTest();
        if ($prsTest) {

            $prsTest->setVehicleVersion($vehicle->getVersion());

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
     * @param CertificateReplacementDraft $draft
     * @param MotTest $motTest
     * @param bool $hasFullRights
     * @param bool $isPsrTest
     * @return MotTest
     */
    protected function updateMotTestFromDraft(
        CertificateReplacementDraft $draft,
        MotTest $motTest,
        $hasFullRights,
        $isPsrTest = false
    )
    {

        $motTest->setOdometerValue($draft->getOdometerValue())
            ->setOdometerUnit($draft->getOdometerUnit())
            ->setOdometerResultType($draft->getOdometerResultType());

        if (!$isPsrTest) {
            $motTest->setExpiryDate($draft->getExpiryDate());
        }

        if ($hasFullRights) {
            $motTest->setVehicleTestingStation($draft->getVehicleTestingStation());

        }

        return $motTest;
    }

    /**
     * @param CertificateReplacementDraft $draft
     * @param MotTest $motTest
     * @param bool $hasFullRights
     * @return \Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle
     */
    private function updateVehicleFromDraftUsingJavaService(CertificateReplacementDraft $draft, MotTest $motTest, $hasFullRights)
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

        $vehicle = $this->vehicleService->getDvsaVehicleByIdAndVersion(
            $motTest->getVehicle()->getId(),
            $motTest->getVehicleVersion()
        );

        if ($this->isVehicleModified($updateVehicleRequest, $vehicle)) {

            if (!$this->motTestRepository->isVehicleLatestTest($motTest)) {
                $updateVehicleRequest->setUpdateHistoricVehicleOnly();
            }

            $vehicle = $this->vehicleService->updateDvsaVehicleAtVersion(
                $motTest->getVehicle()->getId(),
                $motTest->getVehicleVersion(),
                $updateVehicleRequest
            );
        }

        return $vehicle;
    }

    /**
     * @param UpdateDvsaVehicleRequest $updateVehicleRequest
     * @param DvsaVehicle $vehicle
     * @return bool
     */
    private function isVehicleModified(UpdateDvsaVehicleRequest $updateVehicleRequest, DvsaVehicle $vehicle)
    {
        $request = $current = [];

        $request['colour'] = $updateVehicleRequest->getColourCode();
        $request['secondaryColour'] = $updateVehicleRequest->getSecondaryColourCode();
        $request['vin'] = $updateVehicleRequest->getVin();
        $request['vrm'] = $updateVehicleRequest->getRegistration();
        $request['make'] = $updateVehicleRequest->getMakeOther();
        $request['model'] = $updateVehicleRequest->getModelOther();


        $current['colour'] = $vehicle->getColour()->getCode();
        $current['secondaryColour'] = $vehicle->getColourSecondary()->getCode();
        $current['vin'] = $vehicle->getVin();
        $current['vrm'] = $vehicle->getRegistration();
        $current['make'] = $vehicle->getMake()->getName();
        $current['model'] = $vehicle->getModel()->getName();

        $isModified = $request != $current;

        return $isModified;
    }
}
