<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Model\DvsaRole;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use SiteApi\Service\Mapper\SiteBusinessRoleMapper;

/**
 * Class SiteBusinessRoleService.
 */
class SiteBusinessRoleService
{
    private $roleMapper;

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    public function __construct(
        SiteBusinessRoleMapper $roleMapper,
        EntityRepository $siteBusinessRoleRepository,
        SiteBusinessRoleMapRepository $siteBusinessRoleMapRepository,
        AuthorisationServiceInterface $authorisationService
    ) {
        $this->roleMapper = $roleMapper;
        $this->siteBusinessRoleRepository = $siteBusinessRoleRepository;
        $this->siteBusinessRoleMapRepository = $siteBusinessRoleMapRepository;
        $this->authorisationService = $authorisationService;
    }

    public function getListForPerson($nomineeId)
    {
        $availableRoles = [];

        $personRoles = $this->authorisationService->getRolesAsArray($nomineeId);

        if (DvsaRole::containDvsaRole($personRoles)) {
            return $availableRoles;
        }

        $availableRoles = $this->siteBusinessRoleRepository->findAll();

        return $this->roleMapper->manyToArray($availableRoles);
    }

    public function getAssociatedRoles($siteId, $personId)
    {
        $roles = $this->siteBusinessRoleMapRepository->getActiveOrPendingUserRolesInASite($siteId, $personId);

        return $this->roleMapper->convertRoleMapToArray($roles);
    }
}
