<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\AuthorisedExaminerPrincipal;
use DvsaCommonApi\Service\Exception\NotFoundException;

class AuthorisedExaminerPrincipalRepository extends AbstractMutableRepository
{
    /**
     * @param int $authForAeId
     *
     * @return AuthorisedExaminerPrincipal[]
     */
    public function findAllByAuthForAe($authForAeId)
    {
        return $this
            ->createQueryBuilder('aep')
            ->addSelect(['cd', 'a', 'e', 'p'])
            ->innerJoin('aep.contactDetails', 'cd')
            ->innerJoin('cd.address', 'a')
            ->leftJoin('cd.phones', 'p')
            ->leftJoin('cd.emails', 'e')
            ->where('aep.authorisationForAuthorisedExaminer = :authForAe')
            ->setParameter('authForAe', $authForAeId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @param int $authForAeId
     *
     * @return AuthorisedExaminerPrincipal
     *
     * @throws NotFoundException
     */
    public function findByIdAndAuthForAe($id, $authForAeId)
    {
        $aep = $this
            ->createQueryBuilder('aep')
            ->addSelect(['cd', 'a', 'e', 'p'])
            ->innerJoin('aep.contactDetails', 'cd')
            ->innerJoin('cd.address', 'a')
            ->leftJoin('cd.phones', 'p')
            ->leftJoin('cd.emails', 'e')
            ->where('aep.id = :id')
            ->andWhere('aep.authorisationForAuthorisedExaminer = :authForAe')
            ->setParameter('id', $id)
            ->setParameter('authForAe', $authForAeId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($aep === null) {
            throw new NotFoundException('Authorised Examiner Principal');
        }

        return $aep;
    }
}
