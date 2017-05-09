<?php

namespace SiteApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Repository\SiteRepository;
use SiteApi\Service\Mapper\EquipmentMapper;

/**
 * Class EquipmentService.
 */
class EquipmentService extends AbstractService
{
    private $authService;

    /** @var SiteRepository */
    private $siteRepository;
    private $mapper;

    public function __construct(
        SiteRepository $siteRepository,
        AuthorisationServiceInterface $authService
    ) {
        $this->siteRepository = $siteRepository;
        $this->authService = $authService;

        $this->mapper = new EquipmentMapper();
    }

    public function getAllForSite($vtsId)
    {
        // todo wk: authorize when security module is ready

        $site = $this->siteRepository->get($vtsId);
        $dto = $this->mapper->manyToDto($site->getEquipments());

        return $dto;
    }
}
