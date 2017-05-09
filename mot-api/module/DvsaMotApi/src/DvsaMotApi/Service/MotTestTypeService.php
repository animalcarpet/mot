<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\MotTestType;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;

/**
 * Class MotTestTypeService.
 */
class MotTestTypeService extends AbstractService
{
    /** @var \DvsaEntities\Repository\MotTestTypeRepository */
    private $motTestTypeRepository;
    private $objectHydrator;
    private $authService;

    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService
    ) {
        parent::__construct($entityManager);
        $this->motTestTypeRepository = $this->entityManager->getRepository(\DvsaEntities\Entity\MotTestType::class);
        $this->objectHydrator = $objectHydrator;
        $this->authService = $authService;
    }

    public function getMotTestTypeData($id)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_TYPE_READ);

        $data = $this->motTestTypeRepository->findOneByCode($id);
        if (!$data) {
            throw new NotFoundException('MotTestType', $id);
        }

        return $this->extractMotTestTypeData($data);
    }

    private function extractMotTestTypeData($motTestType)
    {
        $motTestTypeData = $this->objectHydrator->extract($motTestType);

        return $motTestTypeData;
    }
}
