<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;

/**
 * OrganisationBusinessRoleMapRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class OrganisationBusinessRoleMapRepository extends AbstractMutableRepository
{
    public function getActiveUserRoles($personId)
    {
        return $this->getUserRoles($personId, BusinessRoleStatusCode::ACTIVE);
    }

    public function getPendingUserRoles($personId)
    {
        return $this->getUserRoles($personId, BusinessRoleStatusCode::PENDING);
    }

    private function getUserRoles($personId, $businessRoleStatusCode)
    {
        $qb = $this
            ->createQueryBuilder('orbm')
            ->addSelect(['p', 'st', 'br', 'org', 'o_cnt', 'cnt_detail', 'addr'])
            ->innerJoin('orbm.person', 'p')
            ->innerJoin('orbm.businessRoleStatus', 'st')
            ->innerJoin('orbm.organisationBusinessRole', 'br')
            ->innerJoin('orbm.organisation', 'org')
            ->leftJoin('org.contacts', 'o_cnt')
            ->leftJoin('o_cnt.contactDetails', 'cnt_detail')
            ->leftJoin('cnt_detail.address', 'addr')
            ->where('p.id = :personId')
            ->andWhere('st.code in (:statusCode)')
            ->setParameter('personId', $personId)
            ->setParameter('statusCode', $businessRoleStatusCode);

        $roles = $qb->getQuery()->getResult();

        return $roles;
    }

    /**
     * @param $id
     *
     * @return OrganisationBusinessRoleMap
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        $position = $this->find($id);

        if ($position === null) {
            throw new NotFoundException('Organisation business position');
        }

        return $position;
    }
}
