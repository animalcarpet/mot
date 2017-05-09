<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityRepository;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaAuthentication\TwoFactorStatus;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Enum\RoleCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\OrganisationBusinessRoleRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaFeature\FeatureToggles;
use NotificationApi\Service\Helper\TwoFactorNotificationTemplateHelper;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use Zend\Authentication\AuthenticationService;

class NominateRoleServiceBuilder
{
    private $personRepository;
    private $organisationRepository;
    private $organisationBusinessRoleRepository;
    private $authorisationService;
    private $conditionalNominationOperation;
    private $directNominationOperation;
    private $transaction;
    private $organisationBusinessRoleMapRepository;

    private $motIdentityProvider;

    private $businessRoleStatusRepository;

    private $featureToggles;

    private $twoFactorStatusService;

    private $roleId;

    private $roleCode;

    public function __construct(
        OrganisationRepository $organisationRepository,
        PersonRepository $personRepository,
        OrganisationBusinessRoleRepository $organisationBusinessRoleRepository,
        EntityRepository $businessRoleStatusRepository,
        OrganisationBusinessRoleMapRepository $organisationBusinessRoleMapRepository,
        AuthorisationServiceInterface $authorisationService,
        ConditionalNominationOperation $conditionalNominationOperation,
        DirectNominationOperation $directNominationOperation,
        Transaction $transaction,
        AuthenticationService $motIdentityProvider,
        FeatureToggles $featureToggles,
        TwoFactorStatusService $twoFactorStatusService
    ) {
        $this->organisationRepository = $organisationRepository;
        $this->personRepository = $personRepository;
        $this->organisationBusinessRoleRepository = $organisationBusinessRoleRepository;
        $this->authorisationService = $authorisationService;
        $this->conditionalNominationOperation = $conditionalNominationOperation;
        $this->directNominationOperation = $directNominationOperation;
        $this->transaction = $transaction;
        $this->authorisationService = $authorisationService;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->businessRoleStatusRepository = $businessRoleStatusRepository;
        $this->organisationBusinessRoleMapRepository = $organisationBusinessRoleMapRepository;
        $this->featureToggles = $featureToggles;
        $this->twoFactorStatusService = $twoFactorStatusService;
    }

    public function buildForNominationCreation($nomineeId, $organisationId, $roleId)
    {
        $nominator = $this->getCurrentUser();

        $nominee = $this->personRepository->get($nomineeId);
        $nomineeTwoFactorStatus = $this->twoFactorStatusService->getStatusForPerson($nominee);

        $organisation = $this->organisationRepository->get($organisationId);

        $organisationBusinessRole = $this->withRoleId($roleId)->getOrganisationBusinessRole();

        $nominationOperation = $this->getNominationOperation($organisationBusinessRole, $nomineeTwoFactorStatus);

        return new NominateRoleService(
            $nominator,
            $nominee,
            $organisation,
            $organisationBusinessRole,
            $this->businessRoleStatusRepository,
            $this->organisationBusinessRoleMapRepository,
            $this->authorisationService,
            $nominationOperation,
            $this->transaction
        );
    }

    public function buildForNominationUpdate($nomineeId, $organisationId, $roleCode)
    {
        $nominee = $this->personRepository->get($nomineeId);
        $nomineeTwoFactorStatus = $this->twoFactorStatusService->getStatusForPerson($nominee);

        $organisation = $this->organisationRepository->get($organisationId);

        $organisationBusinessRole = $this->withRoleCode($roleCode)->getOrganisationBusinessRole();

        $nominationOperation = $this->getNominationOperation($organisationBusinessRole, $nomineeTwoFactorStatus);

        return new NominateRoleService(
            $this->getCurrentUser(),
            $nominee,
            $organisation,
            $organisationBusinessRole,
            $this->businessRoleStatusRepository,
            $this->organisationBusinessRoleMapRepository,
            $this->authorisationService,
            $nominationOperation,
            $this->transaction
        );
    }

    private function getOrganisationBusinessRole()
    {
        $organisationBusinessRole = null;

        if (isset($this->roleId)) {
            $organisationBusinessRole = $this->organisationBusinessRoleRepository->get($this->roleId);
        } elseif (isset($this->roleCode)) {
            $organisationBusinessRole = $this->organisationBusinessRoleRepository->getByCode($this->roleCode);
        }

        if (!isset($organisationBusinessRole)) {
            throw new NotFoundException('Role not found, did you specify an ID or code?');
        }

        return $organisationBusinessRole;
    }

    private function getCurrentUser()
    {
        $personId = $this->motIdentityProvider->getIdentity()->getUserId();

        return $this->personRepository->get($personId);
    }

    private function getNominationOperation(OrganisationBusinessRole $organisationBusinessRole, $nomineeTwoFactorStatus)
    {
        $isDirectNomination = $organisationBusinessRole->getRole()->getCode() == RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;

        $isNomineeTwoFaActive = $nomineeTwoFactorStatus == TwoFactorStatus::ACTIVE;
        $canNomineeSkipTwoFaActivation = $nomineeTwoFactorStatus == TwoFactorStatus::INACTIVE_TRADE_USER;
        $nomineeDoesNotRequireTwoFaActivation = $isNomineeTwoFaActive || $canNomineeSkipTwoFaActivation;

        $isTwoFaToggleEnabled = $this->featureToggles->isEnabled(FeatureToggle::TWO_FA);

        if ($isDirectNomination && ($nomineeDoesNotRequireTwoFaActivation || !$isTwoFaToggleEnabled)) {
            $nominationOperation = $this->directNominationOperation;
        } else {
            if ($isDirectNomination) {
                $this->conditionalNominationOperation->setTwoFactorNotificationTemplateHelper(
                    TwoFactorNotificationTemplateHelper::forPendingDirectNomination($nomineeTwoFactorStatus, $isTwoFaToggleEnabled)
                );
            } else {
                $this->conditionalNominationOperation->setTwoFactorNotificationTemplateHelper(
                    TwoFactorNotificationTemplateHelper::forPendingConditionalNomination($nomineeTwoFactorStatus, $isTwoFaToggleEnabled)
                );
            }

            $nominationOperation = $this->conditionalNominationOperation;
        }

        return $nominationOperation;
    }

    private function withRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    private function withRoleCode($roleCode)
    {
        $this->roleCode = $roleCode;

        return $this;
    }
}
