<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use OrganisationApi\Model\Operation\NominateOperationInterface;
use Exception;

/**
 * Class NominateRoleService.
 */
class NominateRoleService
{
    private $organisationBusinessRole;
    private $organisation;
    private $currentUser;
    private $nominee;
    private $authorisationService;
    private $nominateOperation;
    private $transaction;
    private $organisationBusinessRoleMapRepository;
    private $businessRoleStatusRepository;

    public function __construct(
        Person $currentUser,
        Person $nominee,
        Organisation $organisation,
        OrganisationBusinessRole $organisationBusinessRole,
        EntityRepository $businessRoleStatusRepository,
        OrganisationBusinessRoleMapRepository $organisationBusinessRoleMapRepository,
        AuthorisationServiceInterface $authorisationService,
        NominateOperationInterface $nominateOperation,
        Transaction $transaction
    ) {
        $this->currentUser = $currentUser;
        $this->nominee = $nominee;
        $this->organisation = $organisation;
        $this->organisationBusinessRole = $organisationBusinessRole;
        $this->nominateOperation = $nominateOperation;
        $this->transaction = $transaction;
        $this->authorisationService = $authorisationService;
        $this->businessRoleStatusRepository = $businessRoleStatusRepository;
        $this->organisationBusinessRoleMapRepository = $organisationBusinessRoleMapRepository;
    }

    /**
     * @return OrganisationBusinessRoleMap
     */
    public function nominateRole()
    {
        $this->authorisationService->assertGrantedAtOrganisation(
            PermissionAtOrganisation::NOMINATE_ROLE_AT_AE,
            $this->organisation->getId()
        );

        $nominator = $this->currentUser;

        $organisationPosition = $this->nominateOperation->nominate($nominator, $this->getNomination());

        $this->transaction->flush();

        return $organisationPosition;
    }

    /**
     * @return OrganisationBusinessRoleMap
     *
     * @throws Exception
     */
    public function updateRoleNominationNotification()
    {
        $organisationBusinessRoleMap = $this->getOrganisationBusinessRoleMap(
            $this->organisation->getId(),
            $this->nominee->getId(),
            $this->organisationBusinessRole->getId()
        );

        if (!$organisationBusinessRoleMap) {
            throw new Exception('Organisation Business role map not found');
        }

        $nominator = $organisationBusinessRoleMap->getCreatedBy();

        return $this->nominateOperation->updateNomination($nominator, $organisationBusinessRoleMap);
    }

    private function getNomination()
    {
        $organisation = $this->organisation;

        $status = $this->businessRoleStatusRepository->findOneBy(
            ['code' => BusinessRoleStatusCode::PENDING]
        );

        $map = new OrganisationBusinessRoleMap();
        $map->setPerson($this->nominee)
            ->setOrganisationBusinessRole($this->organisationBusinessRole)
            ->setOrganisation($organisation)
            ->setBusinessRoleStatus($status);

        return $map;
    }

    private function getStatus($code)
    {
        return $this->businessRoleStatusRepository->findOneBy(
            ['code' => $code]
        );
    }

    /**
     * @param $nomineeId
     * @param $roleId
     * @param $siteId
     *
     * @return OrganisationBusinessRoleMap
     */
    private function getOrganisationBusinessRoleMap($organisationId, $nomineeId, $roleId)
    {
        return $this->organisationBusinessRoleMapRepository
            ->findOneBy(
                [
                    'organisation' => $organisationId,
                    'person' => $nomineeId,
                    'organisationBusinessRole' => $roleId,
                    'businessRoleStatus' => $this->getStatus(BusinessRoleStatusCode::PENDING),
                ]
            );
    }
}
