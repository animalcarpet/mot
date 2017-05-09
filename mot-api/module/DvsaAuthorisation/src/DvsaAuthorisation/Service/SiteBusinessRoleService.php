<?php

namespace DvsaAuthorisation\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;

/**
 * Class SiteBusinessRoleService.
 *
 * Allows for a business role to be added to the DB against a site.
 */
class SiteBusinessRoleService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Person $person
     * @param Site   $site
     * @param string $businessRoleCode
     * @param string $statusCode
     */
    public function addSiteBusinessRole(Person $person, Site $site, $businessRoleCode, $statusCode)
    {
        $businessRole = $this->entityManager->getRepository(SiteBusinessRole::class)->get(
            ['code' => $businessRoleCode]
        );
        $status = $this->entityManager->getRepository(BusinessRoleStatus::class)->get(
            ['code' => $statusCode]
        );
        $map = new SiteBusinessRoleMap();
        $map->setPerson($person);
        $map->setSite($site);
        $map->setSiteBusinessRole($businessRole);
        $map->setBusinessRoleStatus($status);

        $this->entityManager->persist($map);
    }
}
