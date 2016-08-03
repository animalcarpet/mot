<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\Person;

/**
 * Class OrganisationRepository
 *
 * @package DvsaEntities\Repository
 * @codeCoverageIgnore
 */
class OrganisationRepository extends AbstractMutableRepository
{

    /**
     * @param $id
     *
     * @return Organisation
     * @throws NotFoundException
     */
    public function get($id)
    {
        $organisation = $this->find($id);

        if ($organisation === null) {
            throw new NotFoundException('Organisation');
        }

        return $organisation;
    }

    /**
     * @param $id
     *
     * @return Organisation
     * @throws NotFoundException
     */
    public function getAuthorisedExaminer($id)
    {
        $organisation = $this->get($id);

        if (!$organisation->isAuthorisedExaminer()) {
            throw new NotFoundException('Authorised Examiner not found');
        }

        return $organisation;
    }

    /**
     * @param $id        organisation ID
     * @param $increment amount of slots (positive or negative)
     */
    public function updateSlotBalance($id, $increment)
    {
        $this->getEntityManager()
            ->createQuery(
                "UPDATE " . Organisation::class
                . " o SET o.slotBalance = (o.slotBalance + :slotsIncrement) WHERE o.id = :id"
            )
            ->setParameter("slotsIncrement", $increment)
            ->setParameter("id", $id)->execute();
    }

    public function findForPersonWithRole(Person $person, OrganisationBusinessRole $role, BusinessRoleStatus $status)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('o')
            ->from(\DvsaEntities\Entity\OrganisationBusinessRoleMap::class, 'obrm')
            ->join(\DvsaEntities\Entity\Organisation::class, 'o', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN,
                'obrm.organisation = o.id')
            ->where('obrm.person = :person')
            ->andWhere('obrm.organisationBusinessRole = :role')
            ->andWhere('obrm.businessRoleStatus = :status')
            ->setParameter('person', $person)
            ->setParameter('role', $role)
            ->setParameter('status', $status);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $siteId
     * @return Organisation
     * @throws NotFoundException
     */
    public function findOrganisationNameBySiteId($siteId)
    {
        $qb = $this->createQueryBuilder("o")
            ->select('o, a')
            ->join('o.sites', 's')
            ->join('o.authorisedExaminer', 'a')
            ->where("s.id = :SITE_ID")
            ->setParameter("SITE_ID", (int)$siteId)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Organisation $entity
     */
    public function persist($entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @param int $aeId
     * @return int
     */
    public function getOrganisationSiteCount($aeId)
    {
        $query = $this->createQueryBuilder('organisation')
            ->select('count(sites.id)')
            ->join('organisation.sites', 'sites')
            ->where('organisation.id = :ORG_ID')
            ->setParameter('ORG_ID', (int)$aeId);

        return (int) $query->getQuery()->getSingleScalarResult();
    }
}
