<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\SeqNumberService;
use DvsaEntities\DqlBuilder\SearchParam\SiteSearchParam;
use DvsaEntities\DqlBuilder\SlotUsageParamDqlBuilder;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;

/**
 * SiteRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class SiteRepository extends AbstractMutableRepository
{
    use SearchRepositoryTrait;

    protected $classes = [];
    protected $statuses = [];
    protected $types = [];
    protected $vehicleClasses = [];

    const TYPE_CTC = 'CTC';
    const TYPE_VTS = 'VTS';
    const TYPE_VRO = 'VRO';
    const TYPE_GVTS = 'GVTS';
    const TYPE_AREA_OFFICE = 'AREA OFFICE';
    const TYPE_SERVICE_DESK = 'SERVICE DESK';
    const TYPE_WELCOMBE_HOUSE = 'WELCOMBE HOUSE';
    const TYPE_BERKELEY_HOUSE = 'BERKELEY HOUSE';
    const TYPE_COURSE_VENUE = 'COURSE VENUE';

    const STATUS_APPLIED = 'APPLIED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_LAPSED = 'LAPSED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_RETRACTED = 'RETRACTED';

    const SEQ_CODE = 'SITENR';

    const ERR_NEXT_SITE_NR_NOT_FOUND = 'Next number of Site was not found';

    /**
     * Initializes a new <tt>EntityRepository</tt>.
     *
     * Fire up the class and add build the static arrays
     *
     * @param EntityManager $em    The EntityManager to use
     * @param ClassMetadata $class The class descriptor
     */
    public function __construct($em, ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->makeTypesArray();
        $this->makeStatusesArray();
        $this->makeVehicleClassesArray();
    }

    /**
     * @param $id
     *
     * @return Site
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        $site = $this->find($id);

        if ($site === null) {
            throw new NotFoundException('Site');
        }

        return $site;
    }

    /**
     * @param $site Site
     */
    public function save($site)
    {
        parent::save($site);
    }

    /**
     * @param $id
     *
     * @return Site
     *
     * @throws NotFoundException
     */
    public function getVehicleTestingStation($id)
    {
        $site = $this->get($id);
        if (!$site->isVehicleTestingStation()) {
            throw new NotFoundException('Vehicle Testing Station not found');
        }

        return $site;
    }

    /**
     * @param $siteNumber
     *
     * @return Site
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getBySiteNumber($siteNumber)
    {
        $result = $this->findBy(['siteNumber' => $siteNumber]);
        if (empty($result)) {
            throw new NotFoundException('Vehicle Testing Station with site number', $siteNumber);
        }

        return $result[0];
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @return array
     */
    public function getVehicleClasses()
    {
        return $this->vehicleClasses;
    }

    protected function makeTypesArray()
    {
        $types = [
            self::TYPE_CTC,
            self::TYPE_VTS,
            self::TYPE_VRO,
            self::TYPE_GVTS,
            self::TYPE_AREA_OFFICE,
            self::TYPE_SERVICE_DESK,
            self::TYPE_WELCOMBE_HOUSE,
            self::TYPE_BERKELEY_HOUSE,
            self::TYPE_COURSE_VENUE,
        ];

        foreach ($types as $type) {
            $this->types[strtolower(str_replace(' ', '_', $type))] = $type;
        }
    }

    protected function makeStatusesArray()
    {
        $statuses = [
            self::STATUS_APPLIED,
            self::STATUS_APPROVED,
            self::STATUS_LAPSED,
            self::STATUS_REJECTED,
            self::STATUS_RETRACTED,
        ];

        foreach ($statuses as $status) {
            $this->statuses[strtolower($status)] = $status;
        }
    }

    protected function makeVehicleClassesArray()
    {
        $this->vehicleClasses = array_combine(VehicleClassCode::getAll(), VehicleClassCode::getAll());
    }

    protected function getSqlBuilder($params)
    {
        return new SlotUsageParamDqlBuilder(
            $this->getEntityManager(),
            $params
        );
    }

    public function findSiteIdsForPersonId($personId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('s.id')
            ->from(SiteBusinessRoleMap::class, 'sbrm')
            ->join(BusinessRoleStatus::class, 'brs', Join::INNER_JOIN, 'sbrm.businessRoleStatus = brs.id')
            ->join(Person::class, 'p', Join::INNER_JOIN, 'sbrm.person = p.id')
            ->join(Site::class, 's', Join::INNER_JOIN, 'sbrm.site = s.id')
            ->where('p.id = :personId')
            ->andWhere('brs.code = :businessRoleStatusCode')
            ->setParameter('personId', $personId)
            ->setParameter('businessRoleStatusCode', BusinessRoleStatusCode::ACTIVE);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findSiteIdsForPersonIdViaOrganisation($personId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('s.id')
            ->from(OrganisationBusinessRoleMap::class, 'obrm')
            ->join(BusinessRoleStatus::class, 'brs', Join::INNER_JOIN, 'obrm.businessRoleStatus = brs.id')
            ->join(Organisation::class, 'o', Join::INNER_JOIN, 'obrm.organisation = o.id')
            ->join(Person::class, 'p', Join::INNER_JOIN, 'obrm.person = p.id')
            ->join(Site::class, 's', Join::INNER_JOIN, 's.organisation = o.id')
            ->where('p.id = :personId')
            ->andWhere('brs.code = :businessRoleStatusCode')
            ->setParameter('personId', $personId)
            ->setParameter('businessRoleStatusCode', BusinessRoleStatusCode::ACTIVE);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function findForPerson(Person $person)
    {
        return $this->getFindForPersonQueryBuilder($person)->getQuery()->getResult();
    }

    public function findForPersonWithRole(Person $person, SiteBusinessRole $role, $status = null)
    {
        $queryBuilder = $this->getFindForPersonQueryBuilder($person);
        $queryBuilder
            ->andWhere('sbrm.siteBusinessRole = :role')
            ->setParameter('role', $role);

        if ($status) {
            $queryBuilder
                ->join(BusinessRoleStatus::class, 'brs', Join::INNER_JOIN, 'sbrm.businessRoleStatus = brs.id')
                ->andWhere('brs.code = :status')
                ->setParameter('status', $status);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    private function getFindForPersonQueryBuilder(Person $person)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('s')
            ->from(SiteBusinessRoleMap::class, 'sbrm')
            ->join(Site::class, 's', Join::INNER_JOIN, 'sbrm.site = s.id')
            ->where('sbrm.person = :person')
            ->setParameter('person', $person);

        return $queryBuilder;
    }

    /**
     * @param int    $personId
     * @param string $roleCode
     * @param string $statusCode
     *
     * @return Site[]
     */
    public function findForPersonIdWithRoleCodeAndStatusCode($personId, $roleCode, $statusCode)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('s')
            ->from(SiteBusinessRoleMap::class, 'sbrm')
            ->join(Person::class, 'p', Join::INNER_JOIN, 'sbrm.person = p.id')
            ->join(Site::class, 's', Join::INNER_JOIN, 'sbrm.site = s.id')
            ->join(SiteBusinessRole::class, 'sbr', Join::INNER_JOIN, 'sbrm.siteBusinessRole = sbr.id')
            ->join(BusinessRoleStatus::class, 'brs', Join::INNER_JOIN, 'sbrm.businessRoleStatus = brs.id')
            ->where('p.id = :personId')
            ->andWhere('sbr.code = :roleCode')
            ->andWhere('brs.code = :statusCode')
            ->setParameter('personId', $personId)
            ->setParameter('roleCode', $roleCode)
            ->setParameter('statusCode', $statusCode);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Return the total result of the search for sites.
     *
     * @param SiteSearchParam $searchParam
     *
     * @return int
     */
    public function findSitesCount(SiteSearchParam $searchParam)
    {
        try {
            return count($this->buildFindSites($searchParam, true)->getResult(AbstractQuery::HYDRATE_SCALAR));
        } catch (\Doctrine\ORM\NoResultException $e) {
            return 0;
        }
    }

    /**
     * Return an array containing the list of site related to a search.
     *
     * @param SiteSearchParam $searchParam
     *
     * @return array
     */
    public function findSites(SiteSearchParam $searchParam)
    {
        return $this->buildFindSites($searchParam)->getResult(AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * Answers an array containing all Site entities that are "APPROVED" and also
     * classified within the "assembly" group of tables as being an "Area Office".
     */
    public function getAllAreaOffices()
    {
        // TODO: Somebody replace this with something DECENT please!
        // We check the length to equal 2 so sub area offices are ignored
        $sql = sprintf(
            'SELECT s.id, s.name, s.site_number, substring(s.site_number,1,2) as ao_number
             FROM site AS s
             INNER JOIN site_type AS st ON st.id=s.type_id
             INNER JOIN site_status_lookup `ssl` ON `ssl`.id=s.site_status_id
             WHERE st.code="%s" AND LENGTH(s.site_number)=2 AND `ssl`.code="%s"
             ORDER BY s.site_number',
            SiteTypeCode::AREA_OFFICE, SiteStatusCode::APPROVED);

        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('site_number', 'siteNumber');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('ao_number', 'areaOfficeNumber');

        /** @var \Doctrine\ORM\NativeQuery $query */
        $query = $this->_em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }

    /**
     * Build the Query to search for a site.
     *
     * @param SiteSearchParam $searchParam
     * @param bool            $isCount
     *
     * @return AbstractQuery|static
     */
    public function buildFindSites(SiteSearchParam $searchParam, $isCount = false)
    {
        $isFullTextSiteNameNumber = (!empty($searchParam->getSiteNumber()) || !empty($searchParam->getSiteName()))
            && !empty($this->buildFullTextSearchSites($searchParam->getSiteNumber(), $searchParam->getSiteName()));

        $isFullTextSiteTownPostcode = (!empty($searchParam->getSiteTown()) || !empty($searchParam->getSitePostcode()))
            && !empty($this->buildFullTextSearchSites($searchParam->getSiteTown(), $searchParam->getSitePostcode()));

        $isSiteVehicleClass = empty($searchParam->getSiteVehicleClass()) === false;

        $sql = $this->buildFindSitesSql(
            $isCount,
            $isFullTextSiteNameNumber,
            $isFullTextSiteTownPostcode,
            $isSiteVehicleClass
        );

        if ($isSiteVehicleClass === true) {
            $sql .= ' HAVING COUNT(DISTINCT vc.code) = :NUMBER_SITE_VEHICLE_CLASS';
        }
        if ($isCount === false) {
            if (is_array($searchParam->getSortColumnNameDatabase())) {
                $orderBy = '';
                foreach ($searchParam->getSortColumnNameDatabase() as $col) {
                    $orderBy .= $orderBy.' '.$col.' '.$searchParam->getSortDirection().',';
                }
                $orderBy = substr($orderBy, 0, strlen($orderBy) - 1);
            } else {
                $orderBy = $searchParam->getSortColumnNameDatabase().' '.$searchParam->getSortDirection();
            }
            $sql .= ' ORDER BY '.$orderBy;
            $sql .= ' LIMIT '.(int) $searchParam->getRowCount().' OFFSET '.(int) $searchParam->getStart();
        }

        $query = $this->_em
            ->createNativeQuery($sql, $this->getResultSetMappingFindSites($isCount))
            ->setParameter(':BUSINESS', 'BUS');

        if ($isFullTextSiteNameNumber === true) {
            $query->setParameter(
                ':SITE_NAME_NUMBER',
                $this->buildFullTextSearchSites($searchParam->getSiteNumber(), $searchParam->getSiteName())
            );
        }
        if ($isFullTextSiteTownPostcode === true) {
            $query->setParameter(
                ':SITE_TOWN_POSTCODE',
                $this->buildFullTextSearchSites($searchParam->getSiteTown(), $searchParam->getSitePostcode())
            );
        }
        if ($isSiteVehicleClass === true) {
            $query->setParameter(':SITE_VEHICLE_CLASS', $searchParam->getSiteVehicleClass());
            $query->setParameter(':NUMBER_SITE_VEHICLE_CLASS', count($searchParam->getSiteVehicleClass()));
        }

        return $query;
    }

    /**
     * Build the native SQL to search for a site.
     *
     * @param $isCount
     * @param $isFullTextSiteNameNumber
     * @param $isFullTextSiteTownPostcode
     * @param $isSiteVehicleClass
     *
     * @return string
     */
    private function buildFindSitesSql(
        $isCount,
        $isFullTextSiteNameNumber,
        $isFullTextSiteTownPostcode,
        $isSiteVehicleClass
    ) {
        $select = ($isCount === true ? 'site.id'
            : '
            site.id,
            site.id as siteId,
            site.site_number,
            site.name,
            p.number as phone,
            a.town,
            a.postcode,
            st.name as type,
            site_status.name as status,
            (SELECT GROUP_CONCAT(DISTINCT vc.code ORDER BY vc.code ASC SEPARATOR \',\')
            FROM site
                LEFT JOIN auth_for_testing_mot_at_site site_auth ON (site.id = site_auth.site_id)
                LEFT JOIN vehicle_class vc ON site_auth.vehicle_class_id = vc.id
            WHERE site.id = siteId
            LIMIT 1) AS roles'
        );

        $sql = "SELECT $select
            FROM site
                LEFT JOIN site_contact_detail_map scdm ON (site.id = scdm.site_id)
                LEFT JOIN contact_detail cd ON (scdm.contact_detail_id = cd.id)
                LEFT JOIN address a ON (cd.address_id = a.id)
                LEFT JOIN site_contact_type sct ON (scdm.site_contact_type_id = sct.id)
                LEFT JOIN phone p ON (p.contact_detail_id = cd.id)
                LEFT JOIN phone_contact_type pct ON (p.phone_contact_type_id = pct.id)
                LEFT JOIN auth_for_testing_mot_at_site site_auth ON (site.id = site_auth.site_id)
                LEFT JOIN site_status_lookup site_status ON site.site_status_id = site_status.id
                LEFT JOIN vehicle_class vc ON site_auth.vehicle_class_id = vc.id
                LEFT JOIN site_type st ON site.type_id = st.id
            WHERE sct.code = :BUSINESS";

        if ($isFullTextSiteNameNumber === true) {
            $sql .= ' AND (MATCH(site.site_number, site.name) AGAINST (:SITE_NAME_NUMBER IN BOOLEAN MODE))';
        }
        if ($isFullTextSiteTownPostcode === true) {
            $sql .= ' AND (MATCH(a.town, a.postcode) AGAINST (:SITE_TOWN_POSTCODE IN BOOLEAN MODE))';
        }

        if ($isSiteVehicleClass === true) {
            $sql .= ' AND vc.code IN (:SITE_VEHICLE_CLASS)';
        }

        $sql .= '
            GROUP BY
                site.id,
                site.site_number,
                site.name,
                p.number,
                a.town,
                a.postcode,
                st.name,
                site_status.name';

        return $sql;
    }

    /**
     * Build the result set map of a search for sites.
     *
     * @param $isCount
     *
     * @return Query\ResultSetMapping
     */
    private function getResultSetMappingFindSites($isCount)
    {
        $rsm = new Query\ResultSetMapping();
        if ($isCount) {
            $rsm->addScalarResult('id', 'id');

            return $rsm;
        }
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('site_number', 'site_number');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('town', 'town');
        $rsm->addScalarResult('postcode', 'postcode');
        $rsm->addScalarResult('roles', 'roles');
        $rsm->addScalarResult('phone', 'phone');
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('status', 'status');

        return $rsm;
    }

    /**
     * Build the full text search for the search of sites.
     *
     * @param $param1
     * @param $param2
     *
     * @return string
     */
    private function buildFullTextSearchSites($param1, $param2)
    {
        $words = array_merge(
            explode(' ', $this->deleteUnwantedChar($param1)),
            explode(' ', $this->deleteUnwantedChar($param2))
        );

        $finalClause = '';
        foreach ($words as $word) {
            if (empty($word) === false && strlen($word) > 2) {
                $finalClause .= ' +'.$word.'*';
            }
        }

        return $finalClause;
    }

    /**
     * Delete the special char use by the full text search to avoid unwanted search.
     *
     * @param string $string
     *
     * @return string
     */
    private function deleteUnwantedChar($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.

        $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
        return str_replace('-', ' ', $string); // Replaces multiple hyphens with single one.
    }

    public function getNextSiteNumber()
    {
        $number = (new SeqNumberService($this->getEntityManager()))->getNextSeqNumber(self::SEQ_CODE);
        if ($number === null) {
            throw new \Exception(self::ERR_NEXT_SITE_NR_NOT_FOUND);
        }

        return $number;
    }

    public function getApprovedUnlinkedSite()
    {
        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('site_number', 'site_number');

        $sql = 'SELECT site.site_number
            FROM site
            WHERE site.organisation_id IS NULL AND site.site_status_id = (SELECT id FROM site_status_lookup WHERE code = :APPROVED)';

        $query = $this->_em
            ->createNativeQuery($sql, $rsm)
            ->setParameter(':APPROVED', SiteStatusCode::APPROVED);

        return $query->getResult(AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * @param int $aeId
     * @param int $offset
     * @param int $itemsPerPage
     *
     * @return Site[]
     */
    public function getSitesTestQualityForOrganisationId($aeId, $offset, $itemsPerPage)
    {
        $queryBuilder = $this->createQueryBuilder('site')
            ->select('site, lastSiteAssessment, siteContacts')
            ->leftJoin('site.lastSiteAssessment', 'lastSiteAssessment')
            ->leftJoin('site.contacts', 'siteContacts')
            ->leftJoin('siteContacts.contactDetail', 'contactDetail')
            ->leftJoin('contactDetail.address', 'address')
            ->where('site.organisation = :ORG_ID')
            ->addOrderBy('lastSiteAssessment.siteAssessmentScore', 'DESC')
            ->addOrderBy('site.name', 'ASC')
            ->setMaxResults((int) $itemsPerPage)
            ->setFirstResult((int) $offset)
            ->setParameter('ORG_ID', (int) $aeId);

        return $queryBuilder->getQuery()->getResult();
    }
}
