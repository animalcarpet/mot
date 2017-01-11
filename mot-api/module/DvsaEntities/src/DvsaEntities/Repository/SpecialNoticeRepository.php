<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Model\DvsaRole;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\SpecialNoticeAudienceTypeId;
use Doctrine\DBAL\Connection;
use DvsaCommon\Utility\ArrayUtils;
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

    const QUERY_BY_USERNAME = 'SELECT sn FROM DvsaEntities\Entity\SpecialNotice sn WHERE sn.username = ?1';

    const QUERY_GET_ALL_CURRENT = 'SELECT snc FROM DvsaEntities\Entity\SpecialNoticeContent snc
                                    WHERE snc.isPublished = 1 AND snc.externalPublishDate <= CURRENT_DATE()';

    const REMOVE_QUERY = 'UPDATE DvsaEntities\Entity\SpecialNotice sn SET sn.isDeleted = true WHERE sn.content = ?1';

    const DVSA_ORG_NAME = 'dvsa';

    public function getAll()
    {
        return $this->findAll();
    }

    public function get($id)
    {
        return $this->find($id);
    }

    public function getLatestIssueNumber()
    {
        $currentYear = (new \DateTime())->format('Y');

        return $this->getEntityManager()
            ->createQuery(self::GET_LATEST_ISSUE_NUMBER_QUERY)
            ->setMaxResults(1)
            ->setParameter(1, $currentYear)
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

    public function getSpecialNoticesForUser($username)
    {
        return $this
            ->getEntityManager()
            ->createQuery(self::QUERY_BY_USERNAME)
            ->setParameter(1, $username)
            ->getResult();
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
            ->setParameter("isPublished", 1)
            ->setParameter("username", $username)
            ->setParameter("publishDate", new \DateTime())
        ;

        return $qb->getQuery()->getResult();
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
            ->setParameter("publishDate", new \DateTime())
            ->setParameter("username", $username)
            ->setParameter("id", $id)
            ->setParameter("isPublished", 1);

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
            ->setParameter("username", $username)
            ->setParameter("contentId", $contentId)
            ->setParameter("isPublished", 1);

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
            ["userId" => $userId, "dvsaRoles" => DvsaRole::getDvsaRoles()],
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

