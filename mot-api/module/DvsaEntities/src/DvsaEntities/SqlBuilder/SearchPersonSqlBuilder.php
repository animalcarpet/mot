<?php

namespace DvsaEntities\SqlBuilder;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Model\SearchPersonModel;

/**
 * Builds SQL for searching person accounts.
 */
class SearchPersonSqlBuilder
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SearchPersonModel
     */
    private $model;

    /**
     * @var array
     */
    private $params;

    /** @var string */
    private $sql;

    /**
     * @param \Doctrine\ORM\EntityManager         $entityManager
     * @param \DvsaCommon\Model\SearchPersonModel $model
     */
    public function __construct(EntityManager $entityManager, SearchPersonModel $model)
    {
        $this->entityManager = $entityManager;
        $this->model = $model;
        $this->params = [];
        $this->buildSqlAndParamsList();
    }

    /**
     * Builds SQL query according to passed parameters.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Builds SQL statement and list of params.
     */
    private function buildSqlAndParamsList()
    {
        $conn = $this->entityManager->getConnection();
        $queryBuilder = $conn->createQueryBuilder();

        $queryBuilder
            ->select(
                'p.id',
                'p.first_name AS firstName',
                'p.family_name AS lastName',
                'p.middle_name AS middleName',
                'p.date_of_birth AS dateOfBirth',
                'p.username',
                'a.postcode',
                'a.town',
                'a.address_line_1 AS addressLine1',
                'a.address_line_2 AS addressLine2',
                'a.address_line_3 AS addressLine3',
                'a.address_line_4 AS addressLine4'
            )
            ->from('person', 'p')
            ->leftJoin('p', 'person_contact_detail_map', 'pc', 'p.id = pc.person_id')
            ->leftJoin('pc', 'contact_detail', 'cd', 'cd.id = pc.contact_id')
            ->where('pc.contact_id IS NULL OR pc.contact_type_id = 1')
            ->leftJoin('cd', 'address', 'a', 'a.id = cd.address_id');

        if (null !== $this->model->getUsername()) {
            $queryBuilder->andWhere('username = :username');
            $this->addParam('username', $this->model->getUsername());
        }

        if (null !== $this->model->getFirstName()) {
            $queryBuilder->andWhere('first_name = :firstName');
            $this->addParam('firstName', $this->model->getFirstName());
        }

        if (null !== $this->model->getLastName()) {
            $queryBuilder->andWhere('family_name = :lastName');
            $this->addParam('lastName', $this->model->getLastName());
        }

        if (null !== $this->model->getEmail()) {
            $queryBuilder->leftJoin('pc', 'email', 'e', 'e.contact_detail_id = pc.contact_id');
            $queryBuilder->andWhere('e.email = :email_address');
            $queryBuilder->andWhere('e.is_primary = 1');
            $this->addParam('email_address', $this->model->getEmail());
        }

        if (null !== $this->model->getDateOfBirth()) {
            $queryBuilder->andWhere('date_of_birth = :dateOfBirth');
            $this->addParam('dateOfBirth', $this->model->getDateOfBirth());
        }

        if (null !== $this->model->getTown()) {
            $queryBuilder->andWhere('town = :town');
            $this->addParam('town', $this->model->getTown());
        }

        if (null !== $this->model->getPostcode()) {
            $queryBuilder->andWhere('postcode = :postcode');
            $this->addParam('postcode', $this->model->getPostcode());
        }

        $queryBuilder->add('orderBy', 'p.family_name ASC, p.first_name ASC, p.middle_name ASC');

        $this->sql = $queryBuilder->getSQL();
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return SearchPersonSqlBuilder
     */
    private function addParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }
}
