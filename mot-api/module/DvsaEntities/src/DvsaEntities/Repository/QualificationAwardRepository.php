<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\AbstractQuery;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\DqlBuilder\SearchParam\DemoTestRequestsSearchParam;
use DvsaEntities\Entity\QualificationAward;

class QualificationAwardRepository extends AbstractMutableRepository
{
    const DEMO_TEST_NEEDED = 'Demo Test Needed';
    const CONTACT_TYPE_PERSONAL = 'PERSONAL';
    const IS_PRIMARY = true;
    /**
     * @param $personId
     *
     * @return QualificationAward[]
     */
    public function findAllByPersonId($personId)
    {
        $query = $this->getDefaultQueryBuilder($personId)->getQuery();

        return $query->getResult();
    }

    /**
     * @param string $vehicleClassGroupCode
     * @param int    $personId
     *
     * @return QualificationAward
     *
     * @throws NotFoundException
     */
    public function getOneByGroupAndPersonId($vehicleClassGroupCode, $personId)
    {
        $query = $this
            ->getDefaultQueryBuilder($personId)
            ->andWhere('vcg.code = :code')
            ->setParameter('code', $vehicleClassGroupCode)
            ->getQuery();

        $result = $query->getOneOrNullResult();

        if ($result === null) {
            throw new NotFoundException($this->getEntityName(), $vehicleClassGroupCode);
        }

        return $result;
    }

    /**
     * @param string $vehicleClassGroupCode
     * @param int    $personId
     *
     * @return QualificationAward
     */
    public function findOneByGroupAndPersonId($vehicleClassGroupCode, $personId)
    {
        $query = $this
            ->getDefaultQueryBuilder($personId)
            ->andWhere('vcg.code = :code')
            ->setParameter('code', $vehicleClassGroupCode)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    private function getDefaultQueryBuilder($personId)
    {
        return $this
            ->createQueryBuilder('mtc')
            ->addSelect(['p', 'vcg', 's'])
            ->leftJoin('mtc.site', 's')
            ->innerJoin('mtc.person', 'p')
            ->innerJoin('mtc.vehicleClassGroup', 'vcg')
            ->where('p.id = :personId')
            ->setParameter('personId', $personId);
    }

    /**
     * @param DemoTestRequestsSearchParam $searchParam
     *
     * @return int
     */
    public function findAllDemoTestRequestsUsersCount(DemoTestRequestsSearchParam $searchParam)
    {
        try {
            return count($this->findAllDemoTestRequestsUsers(true, $searchParam)->getResult(AbstractQuery::HYDRATE_SCALAR));
        } catch (\Doctrine\ORM\NoResultException $e) {
            return 0;
        }
    }

    /**
     * Return an array containing the list of site related to a search.
     *
     * @param DemoTestRequestsSearchParam $searchParam
     *
     * @return array[]
     */
    public function findAllDemoTestRequestsUsersSorted(DemoTestRequestsSearchParam $searchParam)
    {
        return $this->findAllDemoTestRequestsUsers(false, $searchParam)->getResult(AbstractQuery::HYDRATE_SCALAR);
    }

    private function findAllDemoTestRequestsUsers($isCount, DemoTestRequestsSearchParam $searchParam)
    {
        $select = ($isCount === true ? 'qualification_award.id'
            : '
            qualification_award.id,
            person.username,
            phone.number,
            email.email,
            person.firstName,
            person.middleName,
            person.familyName,
            vehicle_class_group.code,
            site.siteNumber,
            address.postcode,
            qualification_award.createdOn'
        );
        $queryBuilder = $this->createQueryBuilder('qualification_award');
        $queryBuilder
            ->distinct(true)
            ->select($select)
            ->leftJoin('qualification_award.site', 'site')
            ->leftJoin('qualification_award.vehicleClassGroup', 'vehicle_class_group')
            ->leftJoin('site.contacts', 'site_contact_details_map')
            ->leftJoin('site_contact_details_map.contactDetail', 'site_contact_details')
            ->leftJoin('site_contact_details.address', 'address')
            ->leftJoin('qualification_award.person', 'person')
            ->leftJoin('person.contacts', 'person_contact_details_map')
            ->leftJoin('person_contact_details_map.contactDetail', 'person_contact_details')
            ->leftJoin('person_contact_details_map.type', 'person_contact_details_type')
            ->leftJoin('person_contact_details.emails', 'email')
            ->leftJoin('person_contact_details.phones', 'phone')
            ->leftJoin('person.authorisationsForTestingMot', 'authorisation_for_testing_mot')
            ->leftJoin('authorisation_for_testing_mot.vehicleClass', 'vehicle_class')
            ->leftJoin('authorisation_for_testing_mot.status', 'authorisation_for_testing_mot_status')
            ->where('person_contact_details_type.name = :contactTypePersonal OR person_contact_details_type.id IS NULL')
            ->andWhere('authorisation_for_testing_mot_status.name = :demoTestNeeded')
            ->andWhere('qualification_award.vehicleClassGroup = vehicle_class.group')
            ->andWhere('phone.isPrimary = :isPrimary OR phone.id IS NULL')
            ->andWhere('email.isPrimary = :isPrimary OR email.id IS NULL')
            ->setParameters(
                [
                    'contactTypePersonal' => self::CONTACT_TYPE_PERSONAL,
                    'demoTestNeeded' => self::DEMO_TEST_NEEDED,
                    'isPrimary' => self::IS_PRIMARY,
                ]
            );

        if ($isCount === false) {
            foreach ($searchParam->getSortColumnNameDatabase() as $col) {
                $queryBuilder->orderBy($col, $searchParam->getSortDirection());
            }
            $queryBuilder->setFirstResult($searchParam->getStart());
            if ($searchParam->getRowCount() != 0) {
                $queryBuilder->setMaxResults($searchParam->getRowCount());
            }
        }

        return $queryBuilder->getQuery();
    }
}
