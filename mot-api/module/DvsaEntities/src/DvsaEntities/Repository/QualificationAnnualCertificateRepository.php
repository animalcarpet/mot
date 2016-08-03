<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\QualificationAnnualCertificate;

class QualificationAnnualCertificateRepository extends AbstractMutableRepository
{
    /**
     * @param int $id
     * @param int $personId
     * @param string $groupCode
     * @return QualificationAnnualCertificate
     * @throws NotFoundException
     */
    public function getOneByIdAndGroupAndPersonId($id, $personId, $groupCode)
    {
        $queryBuilder = $this
            ->createQueryBuilder("qac")
            ->innerJoin("qac.person", "p")
            ->innerJoin("qac.vehicleClassGroup", "vcg")
            ->where("qac.id = :id")
            ->andwhere("p.id = :personId")
            ->andwhere("vcg.code = :vehicleClassGroupCode")
            ->setParameters([
                "id" => $id,
                "personId" => $personId,
                "vehicleClassGroupCode" => $groupCode,
            ]);

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($result === null) {
            throw new NotFoundException($this->getEntityName(), $id);
        }

        return $result;
    }

    public function findAllByGroupAndPersonId($personId, $groupCode)
    {
        $queryBuilder = $this->createQueryBuilder("qac")
            ->innerJoin("qac.person", "p")
            ->innerJoin("qac.vehicleClassGroup", "vcg")
            ->where("p.id = :personId")
            ->andwhere("vcg.code = :vehicleClassGroupCode")
            ->setParameters([
                "personId" => $personId,
                "vehicleClassGroupCode" => $groupCode,
            ])
            ->orderBy("qac.dateAwarded", "DESC");

        return $queryBuilder->getQuery()->getResult();
    }

    /** @return QualificationAnnualCertificate */
    public function getOneById($certificateId)
    {
        return $this->findOneBy(["id" => $certificateId]);
    }
}
