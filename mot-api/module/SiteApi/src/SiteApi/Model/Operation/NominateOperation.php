<?php

namespace SiteApi\Model\Operation;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use SiteApi\Model\NominationVerifier;
use SiteApi\Service\SiteNominationService;

/**
 * Class NominateOperation.
 */
class NominateOperation
{
    private $entityManager;
    private $nominationVerifier;
    private $siteNominationService;

    public function __construct(
        EntityManager $entityManager,
        NominationVerifier $nominationVerifier,
        SiteNominationService $siteNominationService
    ) {
        $this->entityManager = $entityManager;
        $this->nominationVerifier = $nominationVerifier;
        $this->siteNominationService = $siteNominationService;
    }

    /**
     * @param Person              $nominator
     * @param SiteBusinessRoleMap $nomination
     *
     * @return SiteBusinessRoleMap
     */
    public function nominate(Person $nominator, SiteBusinessRoleMap $nomination)
    {
        $this->verifyNomination($nomination);

        $this->entityManager->persist($nomination);
        $this->entityManager->flush($nomination);

        $this->siteNominationService->sendNomination($nominator, $nomination);

        return $nomination;
    }

    /**
     * @param Person              $nominator
     * @param SiteBusinessRoleMap $nomination
     *
     * @return SiteBusinessRoleMap
     */
    public function sendUpdatedNominationNotification(Person $nominator, SiteBusinessRoleMap $nomination)
    {
        $this->siteNominationService->sendNomination($nominator, $nomination);

        return $nomination;
    }

    public function verifyNomination(SiteBusinessRoleMap $nomination)
    {
        $unmetRestrictions = $this->nominationVerifier->verify($nomination);
        $unmetRestrictions->throwIfAny();
    }
}
