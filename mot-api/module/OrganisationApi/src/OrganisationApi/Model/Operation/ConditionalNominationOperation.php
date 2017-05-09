<?php

namespace OrganisationApi\Model\Operation;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use NotificationApi\Service\Helper\TwoFactorNotificationTemplateHelper;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Service\OrganisationNominationNotificationService;

/**
 * Class NominateByRequestOperation.
 *
 * Sends a role nomination to a person.
 * Nominee will need to accept the nomination request first.
 * Before that he cannot fulfill that role.
 */
class ConditionalNominationOperation implements NominateOperationInterface
{
    private $entityManager;
    private $nominationVerifier;
    private $organisationNominationService;

    private $twoFactorNotificationTemplateHelper;

    public function __construct(
        EntityManager $entityManager,
        NominationVerifier $nominationVerifier,
        OrganisationNominationNotificationService $organisationNominationService
    ) {
        $this->entityManager = $entityManager;
        $this->nominationVerifier = $nominationVerifier;
        $this->organisationNominationService = $organisationNominationService;
    }

    /**
     * @param Person                      $nominator
     * @param OrganisationBusinessRoleMap $nomination
     *
     * @return OrganisationBusinessRoleMap
     */
    public function nominate(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        $this->nominationVerifier->verify($nomination);

        $this->entityManager->persist($nomination);
        $this->entityManager->flush($nomination);

        $this->organisationNominationService->sendConditionalNominationNotification($nominator, $nomination, $this->twoFactorNotificationTemplateHelper);

        return $nomination;
    }

    /**
     * @param Person                      $nominator
     * @param OrganisationBusinessRoleMap $nomination
     *
     * @return OrganisationBusinessRoleMap
     */
    public function updateNomination(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        $this->organisationNominationService->sendConditionalNominationNotification(
            $nominator,
            $nomination,
            $this->twoFactorNotificationTemplateHelper
        );

        return $nomination;
    }

    public function setTwoFactorNotificationTemplateHelper(TwoFactorNotificationTemplateHelper $helper)
    {
        $this->twoFactorNotificationTemplateHelper = $helper;
    }
}
