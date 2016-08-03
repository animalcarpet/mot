<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryResult\ComponentFailRateResult;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class ComponentStatisticsRepository extends AbstractStatisticsRepository implements AutoWireableInterface
{
    const PARAM_START_DATE = 'startDate';
    const PARAM_END_DATE = 'endDate';
    const PARAM_GROUP = 'groupCode';

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager);
    }

    protected function mapResult($scalarResult)
    {
        /** @var ComponentFailRateResult[] $dbResults */
        $dbResults = [];

        foreach ($scalarResult as $row) {
            $dbResult = new ComponentFailRateResult();

            $dbResult->setTestItemCategoryName($row['testItemCategoryName']);
            $dbResult->setTestItemCategoryId($row['testItemCategoryId']);
            $dbResult->setFailedCount($row['failedCount']);

            $dbResults[] = $dbResult;
        }

        return $dbResults;
    }

    public function getResult($sql, $params)
    {
        $rsm = $this->getResultSetMapping();

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $this->setParameters($query, $params);

        $scalarResult = $query->getScalarResult();
        return $this->mapResult($scalarResult);
    }

    protected function setParameters(AbstractQuery $query, $params)
    {
        $query->setParameter('failedStatusCode', MotTestStatusCode::FAILED);
        $query->setParameter('passStatusCode', MotTestStatusCode::PASSED);
        $query->setParameter('normalTestCode', MotTestTypeCode::NORMAL_TEST);

        $group = $params[self::PARAM_GROUP];
        $query->setParameter('groupCode', $group);

        $query->setParameter('startDate', $params[self::PARAM_START_DATE]);
        $query->setParameter('endDate', $params[self::PARAM_END_DATE]);
        $query->setParameter('languageTypeCode', LanguageTypeCode::ENGLISH);
        $query->setParameter('irrelevantAssociationCodes',
            [
                OrganisationSiteStatusCode::APPLIED,
                OrganisationSiteStatusCode::UNKNOWN
            ]
        );
        $query->setParameter('skippedRfrTypes', ReasonForRejectionTypeName::ADVISORY);
    }

    protected function getResultSetMapping()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('failedCount', 'failedCount');
        $rsm->addScalarResult('totalCount', 'totalCount');
        $rsm->addScalarResult('testItemCategoryId', 'testItemCategoryId');
        $rsm->addScalarResult('testItemCategoryName', 'testItemCategoryName');

        return $rsm;
    }
}