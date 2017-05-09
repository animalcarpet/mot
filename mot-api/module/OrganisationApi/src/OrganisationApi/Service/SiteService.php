<?php

namespace OrganisationApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaEntities\Repository\OrganisationRepository;
use OrganisationApi\Service\Mapper\SiteMapper;

/**
 * Class SiteService.
 */
class SiteService
{
    /** @var AuthorisationServiceInterface */
    protected $authorisationService;
    /** @var OrganisationRepository */
    private $organisationRepository;
    /** @var SiteMapper */
    private $mapper;

    public function __construct(
        AuthorisationServiceInterface $authSrv,
        OrganisationRepository $organisationRepository,
        SiteMapper $mapper
    ) {
        $this->authorisationService = $authSrv;
        $this->organisationRepository = $organisationRepository;
        $this->mapper = $mapper;
    }

    public function getListForOrganisation($organisationId)
    {
        $this->authorisationService->assertGrantedAtOrganisation(
            PermissionAtOrganisation::VEHICLE_TESTING_STATION_LIST_AT_AE, $organisationId
        );

        $organisation = $this->organisationRepository->get($organisationId);
        $data = $this->mapper->manyToDto($organisation->getSites());

        return $data;
    }
}
