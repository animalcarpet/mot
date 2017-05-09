<?php

namespace OrganisationApi\Model;

use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleId;
use DvsaCommon\Enum\OrganisationBusinessRoleName;
use DvsaCommon\Model\DvsaRole;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;

/**
 * Class RoleAvailability.
 *
 * Provides information what roles are available for people to assign.
 */
class RoleAvailability
{
    /** @var RoleRestrictionsSet */
    private $roleRestrictionsSet;

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var EntityRepository */
    private $organisationBusinessRoleRepository;

    const ERROR_ALREADY_HAS_ROLE = 'This person already has this role';
    const ERROR_ALREADY_HAS_NOMINATION = 'This person has been already nominated to this role';

    public function __construct(
        RoleRestrictionsSet $roleRestrictionsSet,
        AuthorisationServiceInterface $authorisationService,
        EntityRepository $organisationBusinessRoleRepository
    ) {
        $this->roleRestrictionsSet = $roleRestrictionsSet;
        $this->authorisationService = $authorisationService;
        $this->organisationBusinessRoleRepository = $organisationBusinessRoleRepository;
    }

    /**
     * Returns conditions that are not met to allow role assignment.
     *
     * @param OrganisationBusinessRoleMap $nomination
     *
     * @return ErrorSchema
     */
    public function findUnmetRestrictions(OrganisationBusinessRoleMap $nomination)
    {
        $personnel = new OrganisationPersonnel($nomination->getOrganisation());
        $existingPosition = $personnel->findPosition(
            $nomination->getPerson(),
            $nomination->getOrganisationBusinessRole()
        );

        if ($existingPosition) {
            return $this->getAlreadyExistsError($existingPosition);
        }

        $roleRestriction = $this->roleRestrictionsSet->getRestrictionForRole(
            $nomination->getOrganisationBusinessRole()
        );
        $unmetConditions = $roleRestriction->verify($nomination->getPerson(), $personnel);

        return $unmetConditions;
    }

    /**
     * @param $nominee
     * @param $organisation
     *
     * @return OrganisationBusinessRole[]
     */
    public function listAvailableRolesForNominee(Person $nominee, Organisation $organisation)
    {
        return ArrayUtils::filter(
            $this->organisationBusinessRoleRepository->findAll(),
            function (OrganisationBusinessRole $role) use ($nominee, $organisation) {
                $position = new OrganisationBusinessRoleMap();
                $position->setPerson($nominee)
                    ->setOrganisationBusinessRole($role)
                    ->setOrganisation($organisation);
                $unmetRestrictions = $this->findUnmetRestrictions($position);

                return !$unmetRestrictions->hasErrors();
            }
        );
    }

    /**
     * @param Organisation $organisation
     *
     * @return string[]
     */
    public function listRolesNominatorIsPermittedToAssignToPerson(Organisation $organisation, $nomineeId)
    {
        $availableRoles = [];

        $personRoles = $this->authorisationService->getRolesAsArray($nomineeId);

        if (DvsaRole::containDvsaRole($personRoles)) {
            return $availableRoles;
        }

        if ($this->authorisationService->isGranted(PermissionInSystem::NOMINATE_AEDM)) {
            $availableRoles[OrganisationBusinessRoleId::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]
                = OrganisationBusinessRoleName::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;
        }

        if ($this->authorisationService->isGrantedAtOrganisation(
            PermissionAtOrganisation::NOMINATE_ROLE_AT_AE, $organisation->getId()
        )
        ) {
            $availableRoles[OrganisationBusinessRoleId::AUTHORISED_EXAMINER_DELEGATE]
                = OrganisationBusinessRoleName::AUTHORISED_EXAMINER_DELEGATE;
        }

        return $availableRoles;
    }

    private function getAlreadyExistsError(OrganisationBusinessRoleMap $existingPosition)
    {
        $errors = new ErrorSchema();

        if ($existingPosition->getBusinessRoleStatus()->getCode() === BusinessRoleStatusCode::PENDING) {
            $errors->add(self::ERROR_ALREADY_HAS_NOMINATION);
        } else {
            $errors->add(self::ERROR_ALREADY_HAS_ROLE);
        }

        return $errors;
    }
}
