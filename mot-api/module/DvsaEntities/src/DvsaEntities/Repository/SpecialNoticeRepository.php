<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Model\DvsaRole;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\SpecialNoticeAudienceTypeId;
use Doctrine\DBAL\Connection;
use DvsaEntities\Entity\SpecialNotice;
use DvsaCommonApi\Service\Exception\NotFoundException;

/**
 * Class SpecialNoticeRepository
 * @package DvsaEntities\Repository
 * @codeCoverageIgnore
 */
class SpecialNoticeRepository extends AbstractMutableRepository
{

    const GET_LATEST_ISSUE_NUMBER_QUERY =
        'SELECT MAX(snc.issueNumber) FROM DvsaEntities\Entity\SpecialNoticeContent snc WHERE snc.issueYear = ?1';

    // following query has reference in code and is available in API but most likely nothing hit it
    const QUERY_GET_ALL_CURRENT = 'SELECT snc FROM DvsaEntities\Entity\SpecialNoticeContent snc
                                    JOIN DvsaEntities\Entity\SpecialNotice sn WITH sn.contentId = snc.id
                                    WHERE snc.isPublished = 1 AND snc.externalPublishDate <= CURRENT_DATE()
                                    AND snc.isDeleted = 0
                                    AND sn.isDeleted = 0
                                    ORDER BY snc.id DESC
                                    LIMIT 100
                                    ';
    const QUERY_GET_COUNT_ALL_CURRENT = 'SELECT COUNT(snc.id) FROM DvsaEntities\Entity\SpecialNoticeContent snc
                                    JOIN DvsaEntities\Entity\SpecialNotice sn WITH sn.contentId = snc.id
                                    WHERE snc.isPublished = 1 AND snc.externalPublishDate <= CURRENT_DATE()
                                    AND snc.isDeleted = 0
                                    AND sn.isDeleted = 0
                                    ';

    const REMOVE_QUERY = 'UPDATE DvsaEntities\Entity\SpecialNotice sn SET sn.isDeleted = true WHERE sn.content = ?1';

    public function getAll()
    {
        return $this->findAll();
    }

    public function get($id)
    {
        return $this->find($id);
    }

    public function getLatestIssueNumber($year)
    {
        return $this->getEntityManager()
            ->createQuery(self::GET_LATEST_ISSUE_NUMBER_QUERY)
            ->setMaxResults(1)
            ->setParameter(1, $year)
            ->getOneOrNullResult();
    }

    /**
     * @param \ArrayCollection $entities
     */
    public function removeEntities($entities)
    {
        foreach ($entities as $ent) {
            $this->remove($ent);
            $this->flush($ent);
        }
    }

    /**
     * @param string $username
     * @return SpecialNotice[]
     */
    public function getAllCurrentSpecialNoticesForUser($username)
    {
        $qb = $this
            ->createQueryBuilder("sn")
            ->addSelect(["c"])
            ->innerJoin("sn.content", "c")
            ->where("c.isPublished = :isPublished")
            ->andWhere("sn.username = :username")
            ->andWhere("c.externalPublishDate <= :publishDate OR c.internalPublishDate <= :publishDate")
            ->andWhere("sn.isDeleted = :isDeleted")
            ->andWhere("c.isDeleted = :isDeleted")
            ->setParameter("isPublished", 1)
            ->setParameter("username", $username)
            ->setParameter("publishDate", new \DateTime())
            ->setParameter("isDeleted", 0)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getAmountOfOverdueSpecialNoticesForClasses($username)
    {
        $qb = $this
            ->createQueryBuilder("sn")
            ->select("a.vehicleClassId AS vehicleClass", "COUNT(sn.id) as amount")
            ->innerJoin("sn.content", "c")
            ->innerJoin("c.audience", "a")
            ->where("c.isPublished = :isPublished")
            ->andWhere("sn.username = :username")
            ->andWhere("c.expiryDate <= :expiryDate")
            ->andWhere("sn.isAcknowledged = :isAcknowledged")
            ->andWhere("sn.isDeleted = :isDeleted")
            ->andWhere("c.isDeleted = :isDeleted")
            ->groupBy("a.vehicleClassId")
            ->setParameter("isPublished", 1)
            ->setParameter("username", $username)
            ->setParameter("expiryDate", new \DateTime())
            ->setParameter("isAcknowledged", 0)
            ->setParameter("isDeleted", 0)
        ;

        $results = $qb->getQuery()->getScalarResult();

        $codes = VehicleClassCode::getAll();
        $default = array_combine($codes, array_fill(0, count($codes), 0));

        $data = [];
        foreach ($results as $result) {
            $data[$result["vehicleClass"]] = $result["amount"];
        }

        return array_replace($default, $data);
    }

    /**
     * @param int $id
     * @param string $username
     * @return SpecialNotice
     * @throws NotFoundException
     */
    public function getCurrentSpecialNoticeForUser($id, $username)
    {
        $qb = $this
            ->createQueryBuilder("sn")
            ->addSelect(["c"])
            ->innerJoin("sn.content", "c")
            ->where("c.isPublished = :isPublished")
            ->andWhere("sn.username = :username")
            ->andWhere("sn.id = :id")
            ->andWhere("c.externalPublishDate <= :publishDate OR c.internalPublishDate <= :publishDate")
            ->andWhere("sn.isDeleted = :isDeleted")
            ->andWhere("c.isDeleted = :isDeleted")
            ->setParameter("publishDate", new \DateTime())
            ->setParameter("username", $username)
            ->setParameter("id", $id)
            ->setParameter("isPublished", 1)
            ->setParameter("isDeleted", 0)
        ;

        $sn = $qb->getQuery()->getOneOrNullResult();

        if (is_null($sn)) {
            throw new NotFoundException($this->getClassName());
        }

        return $sn;
    }

    /**
     * @param int $contentId
     * @param string $username
     * @return SpecialNotice
     * @throws NotFoundException
     */
    public function getCurrentSpecialNoticeForUserByContentId($contentId, $username)
    {
        $qb = $this
            ->createQueryBuilder("sn")
            ->addSelect(["c"])
            ->innerJoin("sn.content", "c")
            ->where("c.isPublished = :isPublished")
            ->andWhere("sn.username = :username")
            ->andWhere("c.id = :contentId")
            ->andWhere("sn.isDeleted = :isDeleted")
            ->andWhere("c.isDeleted = :isDeleted")
            ->setParameter("username", $username)
            ->setParameter("contentId", $contentId)
            ->setParameter("isPublished", 1)
            ->setParameter("isPublished", 1)
            ->setParameter("isDeleted", 0)
        ;

        $sn = $qb->getQuery()->getOneOrNullResult();

        if (is_null($sn)) {
            throw new NotFoundException($this->getClassName());
        }

        return $sn;
    }


    public function getAllCurrentSpecialNotices()
    {
        return $this
            ->getEntityManager()
            ->createQuery(self::QUERY_GET_ALL_CURRENT)
            ->getResult();
    }

    public function getCountAllCurrentSpecialNotices()
    {
        return $this
            ->getEntityManager()
            ->createQuery(self::QUERY_GET_COUNT_ALL_CURRENT)
            ->getSingleScalarResult();
    }

    public function removeSpecialNoticeContent($id)
    {
        $this
            ->getEntityManager()
            ->createQuery(self::REMOVE_QUERY)
            ->setParameter(1, $id)
            ->execute();
    }

    public function addNewSpecialNotices($userId)
    {
        $conn = $this
            ->getEntityManager()
            ->getConnection();

        $conn->executeQuery(
            $this->getBroadcastInternalSpecialNoticeQuery(),
            ["userId" => $userId, "dvsaRoles" => DvsaRole::getSpecialNoticeRecipientsRoles()],
            ["userId" => \Pdo::PARAM_INT, "dvsaRoles" => Connection::PARAM_STR_ARRAY]
        );

        $conn->executeQuery(
            $this->getBroadcastExternalSpecialNoticeQuery(),
            ["userId" => $userId],
            ["userId" => \Pdo::PARAM_INT]
        );
    }

    private function getBroadcastExternalSpecialNoticeQuery()
    {
        $fromPart = $this->getBroadcastQueryForTesters() . ' UNION ' . $this->getBroadcastQueryForVts();

        return sprintf($this->getBroadcastSpecialNoticeQuery(), $fromPart, ' AND un_sncid.external_publish_date <= CURRENT_DATE');
    }

    private function getBroadcastQueryForTesters()
    {
        return '
                SELECT
                    p.username,
                    snc.id AS special_notice_content_id,
                    snc.is_published,
                    snc.is_deleted,
                    snc.external_publish_date
                FROM
                    special_notice_content snc,
                    special_notice_audience sna,
                    auth_for_testing_mot aftm,
                    auth_for_testing_mot_status aftms,
                    person p
                WHERE
                    snc.id = sna.special_notice_content_id
                    AND p.username IS NOT NULL
                    AND sna.vehicle_class_id = aftm.vehicle_class_id
                    AND aftm.status_id = aftms.id
                    AND aftm.person_id = p.id
                    AND sna.special_notice_audience_type_id = ' . SpecialNoticeAudienceTypeId::TESTER_AUDIENCE . '
                    AND aftms.code = "' . AuthorisationForTestingMotStatusCode::QUALIFIED . '"';
    }

    private function getBroadcastQueryForVts()
    {
        return "
                SELECT
                    p.username,
                    snc.id as special_notice_content_id,
                    snc.is_published,
                    snc.is_deleted,
                    snc.external_publish_date
                FROM
                    special_notice_content snc,
                    special_notice_audience sna,
                    site_business_role_map sbrm,
                    site_business_role sbr,
                    business_role_status brs,
                    person p
                WHERE
                    snc.id = sna.special_notice_content_id
                    AND p.username IS NOT NULL
                    AND sbrm.person_id = p.id
                    AND sbrm.site_business_role_id = sbr.id
                    AND sbrm.status_id = brs.id
                    AND sna.special_notice_audience_type_id = " . SpecialNoticeAudienceTypeId::VTS_AUDIENCE . "
                    AND brs.code = 'AC'
                    AND sbr.code IN
                        ('" . SiteBusinessRoleCode::SITE_MANAGER . "', '" . SiteBusinessRoleCode::SITE_ADMIN . "')
                UNION
                SELECT
                    p.username,
                    snc.id as special_notice_content_id,
                    snc.is_published,
                    snc.is_deleted,
                    snc.external_publish_date
                FROM
                    special_notice_content snc,
                    special_notice_audience sna,
                    organisation_business_role_map obrm,
                    organisation_business_role obr,
                    business_role_status brs,
                    person p
                WHERE
                    snc.id = sna.special_notice_content_id
                    AND p.username IS NOT NULL
                    AND obrm.person_id = p.id
                    AND obrm.status_id = obr.id
                    AND sna.special_notice_audience_type_id = " . SpecialNoticeAudienceTypeId::VTS_AUDIENCE . "
                    AND obrm.status_id = brs.id
                    AND brs.code = 'AC'
                    AND obr.name IN
                        ('" . OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE . "', '"
                            . OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER . "')";
    }

    private function getBroadcastQueryForDvsa()
    {
        return "
                SELECT
                    p.username,
                    snc.id as special_notice_content_id,
                    snc.is_published,
                    snc.is_deleted,
                    snc.internal_publish_date
                FROM
                    special_notice_content snc,
                    special_notice_audience sna,
                    person_system_role_map psrm,
                    person_system_role psr,
                    business_role_status brs,
                    person p
                WHERE
                    snc.id = sna.special_notice_content_id
                    AND p.username IS NOT NULL
                    AND psrm.person_id = p.id
                    AND sna.special_notice_audience_type_id = " . SpecialNoticeAudienceTypeId::DVSA_AUDIENCE . "
                    AND psrm.status_id = brs.id
                    AND psrm.person_system_role_id = psr.id
                    AND brs.code = 'AC'
                    AND psr.name in
                        (:dvsaRoles)
                    ";
    }

    private function getBroadcastInternalSpecialNoticeQuery()
    {
        return sprintf($this->getBroadcastSpecialNoticeQuery(), $this->getBroadcastQueryForDvsa(), ' AND un_sncid.internal_publish_date <= CURRENT_DATE');
    }

    private function getBroadcastSpecialNoticeQuery()
    {
        return '
        INSERT INTO special_notice(username, special_notice_content_id, created_on, created_by)
        SELECT DISTINCT
            un_sncid.username,
            un_sncid.special_notice_content_id,
            CURRENT_TIMESTAMP,
            :userId
        FROM (
            %s
            ) un_sncid
        LEFT OUTER JOIN special_notice sn
            ON (un_sncid.username = sn.username
            AND un_sncid.special_notice_content_id = sn.special_notice_content_id)
        WHERE
            sn.username IS NULL
            AND sn.special_notice_content_id IS NULL
            AND un_sncid.is_published = 1
            AND un_sncid.is_deleted = 0
            %s';
    }
}

