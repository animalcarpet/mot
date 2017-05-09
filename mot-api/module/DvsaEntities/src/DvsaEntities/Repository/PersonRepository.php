<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use DvsaCommon\Constants\PersonContactType;
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
}
