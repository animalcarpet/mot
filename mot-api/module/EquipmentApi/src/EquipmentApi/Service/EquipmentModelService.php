<?php

namespace EquipmentApi\Service;

use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaEntities\Entity\EquipmentModel;
use EquipmentApi\Mapper\EquipmentModelMapper;

/**
 * Class EquipmentModelService.
 */
class EquipmentModelService
{
    private $equipmentModelRepository;
    private $authService;
    private $mapper;

    public function __construct(EntityRepository $equipmentModelRepository, AuthorisationServiceInterface $authService)
    {
        $this->equipmentModelRepository = $equipmentModelRepository;
        $this->authService = $authService;
        $this->mapper = new EquipmentModelMapper();
    }

    /**
     * @return EquipmentModel[]
     */
    public function getAll()
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_CAN_VIEW_EQUIPMENT);

        $models = $this->equipmentModelRepository->findAll();

        return $this->mapper->manyToDto($models);
    }
}
