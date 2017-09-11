<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Model\SearchPersonModel;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\DqlBuilder\TesterSearchParamDqlBuilder;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\SqlBuilder\SearchPersonSqlBuilder;

/**
 * Repository for {@link \DvsaEntities\Entity\Person}.
 */
class PersonRepository extends AbstractMutableRepository
{
    use SearchRepositoryTrait;

    /**
     * Gets person by id.
     *
     * @param int $id
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Person
     */
    public function get($id)
    {
        $person = $this->find($id);
        if (null === $person) {
            throw new NotFoundException('Person '.$id.' not found');
        }

        return $person;
    }

    /**
     * @param mixed $id
     * @param null  $lockMode
     * @param null  $lockVersion
     *
     * @return Person
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * Gets a person by id or username, in that order.
     *
     * @param mixed $userId or $username
     *
     * @return Person
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByIdOrUsername($idOrUsername)
    {
        $person = $this->find($idOrUsername);
        if (!$person) {
            $person = $this->findOneBy(['username' => $idOrUsername]);
        }

        if (!$person) {
            throw new NotFoundException('Person '.$idOrUsername.' not found');
        }

        return $person;
    }

    /**
     * Gets person by username.
     *
     * @param string $login
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Person
     */
    public function getByIdentifier($login)
    {
        $person = $this->findOneBy(['username' => $login]);
        if (null === $person) {
            throw new NotFoundException('Person '.$login.' not found');
        }

        return $person;
    }

    /**
     * Retrieves all done necessary to set up identity in one query.
     *
     * @param $username
     *
     * @return null|Person
     */
    public function findIdentity($username)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
                ->select('p, am')
                ->from(Person::class, 'p')
                ->join(AuthenticationMethod::class, 'am', Join::INNER_JOIN, 'p.authenticationMethod = am.id')
                ->where('p.username = :username')
                ->setParameter('username', $username);

        $result = $queryBuilder->getQuery()->getResult();

        return empty($result) ? null : $result[0];
    }

    /**
     * Gets person by the user_reference field value.
     *
     * @param string $user_reference
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Person
     */
    public function getByUserReference($user_reference)
    {
        $person = $this->findOneBy(['userReference' => $user_reference]);

        if (null === $person) {
            throw new NotFoundException('Person/user_ref: '.$user_reference.' not found');
        }

        return $person;
    }

    /**
     * Returns array of associative array. Can be an empty array either.
     * [
     *  [
     *     id => int,
     *     firstName => string,
     *     lastName => string,
     *     dateOfBirth => string,
     *     postcode => string,
     *     town => string,
     *     addressLine1 => string,
     *     addressLine2 => string,
     *     addressLine3 => string,
     *     addressLine4 => string,
     *  ], ...
     * ].
     *
     * @param SearchPersonModel $searchPerson
     *
     * @throws BadRequestException
     *
     * @return array
     */
    public function searchAll(SearchPersonModel $searchPerson)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sqlBuilder = new SearchPersonSqlBuilder($this->getEntityManager(), $searchPerson);

        $sql = $sqlBuilder->getSql();
        $params = $sqlBuilder->getParams();

        $stmt = $conn->executeQuery($sql, $params);
        $result = $stmt->fetchAll();

        $stmt->closeCursor();
        $conn->close();

        return $result;
    }

    /**
     * Gets the site Count for sites associated with supplied person and for the specified role.
     *
     * @param int    $personId
     * @param string $roleCode
     * @param string $statusCode
     *
     * @return array
     */
    public function getSiteCount($personId, $roleCode, $statusCode)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('count(s.id)')
            ->from(SiteBusinessRoleMap::class, 'sbrm')
            ->join(Site::class, 's', Join::INNER_JOIN, 'sbrm.site = s.id')
            ->join(SiteBusinessRole::class, 'sbr', Join::INNER_JOIN, 'sbrm.siteBusinessRole = sbr.id')
            ->join(Person::class, 'p', Join::INNER_JOIN, 'sbrm.person = p.id')
            ->join(BusinessRoleStatus::class, 'brs', Join::INNER_JOIN, 'sbrm.businessRoleStatus = brs.id')
            ->where('p.id = :personId')
            ->andWhere('sbr.code = :roleCode')
            ->andWhere('brs.code = :statusCode')
            ->setParameter('personId', $personId)
            ->setParameter('roleCode', $roleCode)
            ->setParameter('statusCode', $statusCode);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $params
     *
     * @return TesterSearchParamDqlBuilder
     */
    protected function getSqlBuilder($params)
    {
        // default search handler -> tester
        return new TesterSearchParamDqlBuilder(
            $this->getEntityManager(),
            $params
        );
    }

    /**
     * For use in New User Registration
     * Passes in the letters of the user's username and assigns a number to them based on the la.
     *
     * @param string $username
     *
     * @return mixed|null
     */
    public function getLastUsername($username, $lowerLimit, $upperLimit)
    {
        $lowerLimit = $username.$lowerLimit;
        $upperLimit = $username.$upperLimit;
        $username = $username.'%';

        // Query set to use Upper and Lower limits from DBA advice
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('MAX(person.username)')
            ->from(Person::class, 'person')
            ->where('person.username LIKE :username')
            ->andWhere('person.username > :lowerLimit')
            ->andWhere('person.username <= :upperLimit')
            ->setParameter('username', $username)
            ->setParameter('lowerLimit', $lowerLimit)
            ->setParameter('upperLimit', $upperLimit)
            ->setMaxResults(1);

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return isset($result['1']) ? $result['1'] : null;
    }

    /**
     * @param int               $personId
     * @param PersonContactType $contactType
     *
     * @return string|null
     */
    public function findPersonEmail($personId, $contactType)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('_emails.email')
            ->from(Person::class, '_person')
            ->join('_person.contacts', '_contacts')
            ->join('_contacts.contactDetail', '_contactDetail')
            ->join('_contactDetail.emails', '_emails')
            ->where('_person.id = :personId')
            ->andWhere('_contacts.type = :contactTypeId')
            ->setParameter('personId', $personId)
            ->setParameter('contactTypeId', $contactType->getId())
            ->setMaxResults(1); // db allows to have multiple personal addresses

        try {
            $result = $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * Gets all people with organisation records where they have an AED or AEDM role
     *
     * @return array
     */
    public function findAllAEDandAEDMS(): array
    {
        $sql = $this->getAllAEDAndAEDMSQuery();

        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping
            ->addScalarResult('personId', 'personId')
            ->addScalarResult('id', 'id')
            ->addScalarResult('orgName', 'orgName')
            ->addScalarResult('orgRef', 'orgRef')
            ;

        $query = $this->getEntityManager()->createNativeQuery($sql, $resultSetMapping);
        $query->setParameter("AED_ROLE_CODE", RoleCode::AUTHORISED_EXAMINER_DELEGATE);
        $query->setParameter("AEDM_ROLE_CODE", RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);
        $query->setParameter("BUSINESS_ROLE_STATUS_CODE", BusinessRoleStatusCode::ACTIVE);
        $query->setParameter("AUTH_FOR_AE_STATUS_CODE", AuthorisationForAuthorisedExaminerStatusCode::APPROVED);

        return $query->getScalarResult();
    }

    /**
     * Gets all people with their sites in which they have Site Manager role as long as they do not have
     * AED or AEDM role in a organisation that is associated with those sites
     *
     * @return array
     */
    public function findAllSiteManagers(): array
    {
        $sql = $this->getAllSMQuery();

        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping
            ->addScalarResult('personId', 'personId')
            ->addScalarResult('siteId', 'id')
            ->addScalarResult('siteName', 'siteName')
            ->addScalarResult('siteRef', 'siteRef')
            ->addScalarResult('addressLine1', 'addressLine1')
            ->addScalarResult('addressLine2', 'addressLine2')
            ->addScalarResult('addressLine3', 'addressLine3')
            ->addScalarResult('addressLine4', 'addressLine4')
            ->addScalarResult('country', 'country')
            ->addScalarResult('town', 'town')
            ->addScalarResult('postcode', 'postcode')
        ;

        $query = $this->getEntityManager()->createNativeQuery($sql, $resultSetMapping);
        $query->setParameter("AED_ROLE_CODE", RoleCode::AUTHORISED_EXAMINER_DELEGATE);
        $query->setParameter("AEDM_ROLE_CODE", RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);
        $query->setParameter("SM_ROLE_CODE", RoleCode::SITE_MANAGER);
        $query->setParameter("BUSINESS_ROLE_STATUS_CODE", BusinessRoleStatusCode::ACTIVE);
        $query->setParameter("AUTH_FOR_AE_STATUS_CODE", AuthorisationForAuthorisedExaminerStatusCode::APPROVED);
        $query->setParameter("SITE_STATUS_CODE", SiteStatusCode::APPROVED);
        $query->setParameter("ADDRESS_TYPE", SiteContactTypeCode::BUSINESS);

        return $query->getScalarResult();
    }

    private function getAllAEDAndAEDMSQuery():string
    {
        return "
            SELECT `person`.`id` AS `personId`, `org`.`id` AS `id`, `org`.`name` AS `orgName`, `afae`.`ae_ref` AS `orgRef`
            FROM `person`
                JOIN `organisation_business_role_map` `obrm` ON `obrm`.`person_id` = `person`.`id`
                JOIN `organisation_business_role` `obr` ON `obr`.`id` = `obrm`.`business_role_id`
                JOIN `business_role_status` `brs` ON `brs`.`id` = `obrm`.`status_id`
                JOIN `organisation` `org` ON `org`.`id` = `obrm`.`organisation_id`
                JOIN `auth_for_ae` `afae` ON `afae`.`organisation_id` = `org`.`id`
                JOIN `auth_for_ae_status` `afaes` ON `afaes`.`id` = `afae`.`status_id`
                JOIN `role` `role` ON `role`.`id` = `obr`.`role_id`
            WHERE `role`.`code` IN(:AED_ROLE_CODE, :AEDM_ROLE_CODE)
                AND `brs`.`code` = :BUSINESS_ROLE_STATUS_CODE
                AND `afaes`.`code` = :AUTH_FOR_AE_STATUS_CODE
            GROUP BY `id`, `personId`
        ";
    }

    private function getAllSMQuery():string
    {
        return "
            SELECT `p1`.`id` AS `personId`,
                `site`.`id` AS `siteId`,
                `site`.`name` AS `siteName`,
                `site`.`site_number` AS `siteRef`,
                `address`.`address_line_1` AS `addressLine1`,
                `address`.`address_line_2` AS `addressLine2`,
                `address`.`address_line_3` AS `addressLine3`,
                `address`.`address_line_4` AS `addressLine4`,
                `address`.`country` AS `country`,
                `address`.`town` AS `town`,
                `address`.`postcode` AS `postcode`
            FROM `person` `p1`
                JOIN `site_business_role_map` `sbrm` ON `sbrm`.`person_id` = `p1`.`id`
                JOIN `site_business_role` `sbr` ON `sbr`.`id` = `sbrm`.`site_business_role_id`
                JOIN `business_role_status` `brss` ON `brss`.`id` = `sbrm`.`status_id`
                JOIN `site` ON `site`.`id` = `sbrm`.`site_id`
                JOIN `site_status_lookup` `ssl` ON `ssl`.`id` = `site`.`site_status_id`
                JOIN `role` `site_role` ON `site_role`.`id` = `sbr`.`role_id`
                LEFT JOIN `site_contact_detail_map` `scdm` on `site`.`id` = `scdm`.`site_id`
                    AND `scdm`.`site_contact_type_id` = (
                        SELECT `sct`.`id`
                        FROM `site_contact_type` `sct`
                        WHERE `sct`.`code` = :ADDRESS_TYPE
                    )
                LEFT JOIN `contact_detail` `cd` on `cd`.`id` = `scdm`.`contact_detail_id`
                LEFT JOIN `address` ON `cd`.`address_id` = `address`.`id`
            WHERE `site_role`.`code` = :SM_ROLE_CODE
                AND `brss`.`code` = :BUSINESS_ROLE_STATUS_CODE
                AND `ssl`.`code` = :SITE_STATUS_CODE
                AND NOT EXISTS (
                    SELECT `org`.`id`
                    FROM `organisation_business_role_map` `obrm`
                        JOIN `organisation_business_role` `obr` ON `obr`.`id` = `obrm`.`business_role_id`
                        JOIN `business_role_status` `brs` ON `brs`.`id` = `obrm`.`status_id`
                        JOIN `organisation` `org` ON `org`.`id` = `obrm`.`organisation_id`
                        JOIN `auth_for_ae` `afae` ON `afae`.`organisation_id` = `org`.`id`
                        JOIN `auth_for_ae_status` `afaes` ON `afaes`.`id` = `afae`.`status_id`
                        JOIN `role` `org_role` ON `org_role`.`id` = `obr`.`role_id`
                        JOIN `organisation_site_map` `osm` ON `org`.`id` = `osm`.`organisation_id`
                    WHERE `org_role`.`code` IN(:AED_ROLE_CODE, :AEDM_ROLE_CODE)
                        AND `brs`.`code` = :BUSINESS_ROLE_STATUS_CODE
                        AND `afaes`.`code` = :AUTH_FOR_AE_STATUS_CODE
                        AND `obrm`.`person_id` = `p1`.`id`
                        AND `osm`.`site_id` = `site`.`id`
                    GROUP BY `obrm`.`organisation_id`
                )";
    }
}
