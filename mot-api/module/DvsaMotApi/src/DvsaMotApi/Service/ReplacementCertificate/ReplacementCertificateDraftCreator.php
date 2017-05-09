<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service\ReplacementCertificate;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Entity;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Service\MotTestSecurityService;

/**
 * Class ReplacementCertificateDraftCreator.
 */
class ReplacementCertificateDraftCreator
{
    /**
     * @var \DvsaAuthorisation\Service\AuthorisationServiceInterface
     */
    private $authorizationService;
    /**
     * @var \DvsaMotApi\Service\MotTestSecurityService
     */
    private $motTestSecurityService;

    /** @var VehicleService */
    private $vehicleService;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @param MotTestSecurityService        $motTestSecurityService
     * @param AuthorisationServiceInterface $authorizationService
     * @param VehicleService                $vehicleService
     * @param EntityManager                 $entityManager
     */
    public function __construct(
        MotTestSecurityService $motTestSecurityService,
        AuthorisationServiceInterface $authorizationService,
        VehicleService $vehicleService,
        EntityManager $entityManager
    ) {
        $this->motTestSecurityService = $motTestSecurityService;
        $this->authorizationService = $authorizationService;
        $this->vehicleService = $vehicleService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param MotTest $motTest
     *
     * @return CertificateReplacementDraft
     *
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function create(MotTest $motTest, $replacementReason = '')
    {
        $hasFullRights = $this->authorizationService->isGranted(PermissionInSystem::CERTIFICATE_REPLACEMENT_SPECIAL_FIELDS);

        if (!$motTest->isPassedOrFailed()) {
            throw new ForbiddenException('Mot test is neither PASSED nor FAILED');
        }

        $vtsId = $motTest->getVehicleTestingStation()->getId();
        // to be reviewed: implicit assumption: !ADMIN => TESTER
        if (!$hasFullRights && !$this->motTestSecurityService->isCurrentTesterAssignedToVts($vtsId)) {
            throw new ForbiddenException(
                'Current user is not allowed to replace this certificate since he is not registered
                 in the VTS the certificate was issued at'
            );
        }

        $vehicle = $this->vehicleService->getDvsaVehicleByIdAndVersion(
            $motTest->getVehicle()->getId(),
            $motTest->getVehicleVersion()
        );

        $primaryColour = $this->getColourByCode($vehicle->getColour()->getCode());
        $make = $this->getMakeById($vehicle->getMake()->getId());
        $model = $this->getModelById($vehicle->getModel()->getId());

        $fetchedEntities = [$make, $model, $primaryColour];

        if (!is_null($vehicle->getColourSecondary()->getCode())) {
            $secondaryColour = $this->getColourByCode($vehicle->getColourSecondary()->getCode());
            $fetchedEntities[] = $secondaryColour;
        }

        $this->assertInstanceOf(Entity::class, $fetchedEntities);

        $draft = CertificateReplacementDraft::create()
            ->setMotTest($motTest)
            ->setMotTestVersion($motTest->getVersion())
            ->setPrimaryColour($primaryColour)
            ->setExpiryDate($motTest->getExpiryDate())
            ->setVrm($vehicle->getRegistration())
            ->setVin($vehicle->getVin())
            ->setMake($make)
            ->setMakeName($vehicle->getMakeName())
            ->setModel($model)
            ->setModelName($vehicle->getModelName())
            ->setCountryOfRegistration($motTest->getCountryOfRegistration())
            ->setExpiryDate($motTest->getExpiryDate())
            ->setVehicleTestingStation($motTest->getVehicleTestingStation())
            ->setReasonForReplacement($replacementReason)
            ->setVinVrmExpiryChanged(false)
            ->setOdometerValue($motTest->getOdometerValue())
            ->setOdometerUnit($motTest->getOdometerUnit())
            ->setOdometerResultType($motTest->getOdometerResultType());

        if (isset($secondaryColour)) {
            $draft->setSecondaryColour($secondaryColour);
        }

        return $draft;
    }

    /**
     * @param string $code
     *
     * @return null|Colour
     */
    private function getColourByCode($code)
    {
        return $this->entityManager->getRepository(Colour::class)->getByCode($code);
    }

    /**
     * @param int $id
     *
     * @return Make|null
     */
    private function getMakeById($id)
    {
        if (null === $id) {
            return;
        }

        return $this->entityManager->getRepository(Make::class)->get($id);
    }

    /**
     * @param int $id
     *
     * @return Model|null
     */
    private function getModelById($id)
    {
        if (null === $id) {
            return;
        }

        return $this->entityManager->getRepository(Model::class)->get($id);
    }

    private function assertInstanceOf($class, $objects)
    {
        foreach ($objects as $object) {
            if (!$object instanceof $class) {
                throw new \RuntimeException(sprintf(
                    'Expected instance of %s, but received %s',
                    $class,
                    get_class($object)
                ));
            }
        }
    }
}
