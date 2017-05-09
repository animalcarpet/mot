<?php

namespace OrganisationApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\PersonRepository;
use OrganisationApi\Model\RoleAvailability;
use Zend\Authentication\AuthenticationService;

/**
 * Class OrganisationRoleService.
 */
class OrganisationRoleService
{
    private $authorizationService;
    private $organisationRepository;
    private $personRepository;
    private $nominationVerifier;

    /** @var AuthenticationService $motIdentityProvider */
    private $motIdentityProvider;

    public function __construct(
        AuthorisationServiceInterface $authorizationService,
        OrganisationRepository $organisationRepository,
        PersonRepository $personRepository,
        RoleAvailability $nominationVerifier,
        AuthenticationService $motIdentityProvider
    ) {
        $this->organisationRepository = $organisationRepository;
        $this->personRepository = $personRepository;
        $this->authorizationService = $authorizationService;
        $this->nominationVerifier = $nominationVerifier;
        $this->motIdentityProvider = $motIdentityProvider;
    }

    /**
     * @param int $organisationId
     * @param int $nomineeId      Person's Id who going to be nominated for one of returning role
     *
     * @return array array of strings
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getListForPerson($organisationId,  $nomineeId)
    {
        $organisation = $this->organisationRepository->get($organisationId);

        $roles = $this->nominationVerifier->listRolesNominatorIsPermittedToAssignToPerson($organisation, $nomineeId);

        return $roles;
    }
}
