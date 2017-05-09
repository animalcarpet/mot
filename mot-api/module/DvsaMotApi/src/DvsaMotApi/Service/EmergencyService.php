<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;

/**
 * Class EmergencyService.
 *
 * This provides a means to obtain the emergency reason codes and
 * for validating them. It also provides an entry point that allows
 * for the validation of a contingency code.
 */
class EmergencyService extends AbstractService
{
    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;
    /**
     * @var DoctrineObject
     */
    private $objectHydrator;
    private $repoReason;
    private $repoEmergencyLog;

    /**
     * Creates the Emergency service class.
     *
     * @param AuthorisationServiceInterface $authService
     * @param EntityManager                 $entityManager
     * @param DoctrineObject                $objectHydrator
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        EntityManager $entityManager,
        DoctrineObject $objectHydrator
    ) {
        parent::__construct($entityManager);

        $this->authService = $authService;
        $this->repoReason = $this->entityManager->getRepository(\DvsaEntities\Entity\EmergencyReason::class);
        $this->repoEmergencyLog = $this->entityManager->getRepository(\DvsaEntities\Entity\EmergencyLog::class);
        $this->objectHydrator = $objectHydrator;
    }

    /**
     * Returns all emergency codes.
     *
     * @return array
     */
    public function getAllEmergencyReasonCodes()
    {
        $this->authService->assertGranted(PermissionInSystem::EMERGENCY_TEST_READ);
        $codes = $this->repoReason->findAll();

        return $codes;
    }

    /**
     * Get a single emergency reason code by its database id value.
     *
     * @param $reasonId
     *
     * @return null|object
     *
     * @throws NotFoundException
     */
    public function getEmergencyCodeById($reasonId)
    {
        $this->authService->assertGranted(PermissionInSystem::EMERGENCY_TEST_READ);
        $reason = $this->repoReason->find($reasonId);

        if (!$reason) {
            throw new NotFoundException('EmergencyReason', $reasonId);
        }

        return $reason;
    }

    /**
     * Find an emergency reason by its assigned code value.
     *
     * @param $code
     *
     * @return mixed
     */
    public function getEmergencyByCode($code)
    {
        $this->authService->assertGranted(PermissionInSystem::EMERGENCY_TEST_READ);

        return $this->repoReason->findOneBy(['code' => $code]);
    }

    /**
     * Find an emergency log entry by its emergency code (contingency code).
     *
     * @param $number
     *
     * @return null|object
     */
    public function getEmergencyLog($number)
    {
        $this->authService->assertGranted(PermissionInSystem::EMERGENCY_TEST_READ);

        return $this->repoEmergencyLog->findOneBy(['number' => $number]);
    }
}
