<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\OrganisationRepository;
use OrganisationApi\Service\Mapper\OrganisationMapper;

/**
 * Class OrganisationService.
 */
class OrganisationService extends AbstractService
{
    private $mapper;

    /**
     * @var \DvsaEntities\Repository\OrganisationRepository
     */
    private $organisationRepository;

    public function __construct(
        EntityManager $entityManager,
        OrganisationRepository $organisationRepository,
        OrganisationMapper $mapper
    ) {
        parent::__construct($entityManager);
        $this->entityManager = $entityManager;
        $this->organisationRepository = $organisationRepository;
        $this->mapper = $mapper;
    }

    /**
     * @param $siteId
     *
     * @return array of organisation id and name
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function findOrganisationNameBySiteId($siteId)
    {
        $organisation = $this->organisationRepository->findOrganisationNameBySiteId($siteId);
        if (is_null($organisation)) {
            return [];
        }

        return [
            'id' => $organisation->getId(),
            'name' => $organisation->getName(),
        ];
    }

    /**
     * Don't call this within a long-running transaction - it will result in
     * lock contention.
     */
    public function incrementSlotBalance(Organisation $organisation)
    {
        $this->organisationRepository->updateSlotBalance($organisation->getId(), 1);
    }

    /**
     * Don't call this within a long-running transaction - it will result in
     * lock contention.
     */
    public function decrementSlotBalance(Organisation $organisation)
    {
        $this->organisationRepository->updateSlotBalance($organisation->getId(), -1);
    }
}
