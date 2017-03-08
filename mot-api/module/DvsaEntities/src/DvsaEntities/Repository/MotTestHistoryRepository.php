<?php

namespace DvsaEntities\Repository;

use DateTime;
use Doctrine\ORM\NoResultException;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Repository\Query\MotTestSearchQuery;
use DvsaMotApi\Helper\MysteryShopperHelper;

/**
 * This repository encapsulates most of logic related to querying the mot_test_history table, where
 * older mot test records are stored.
 */
class MotTestHistoryRepository extends MotTestRepository
{
    const SUFFIX_CURRENT = 'current';
    const SUFFIX_HISTORY = 'history';

    /**
     * {@inheritdoc}
     */
    public function findTestsForVehicle(
        $vehicleId,
        $startDate,
        MysteryShopperHelper $mysteryShopperHelper,
        array $mysteryShopperSiteIds = []
    ) {
        try {
            $currentTests = parent::findTestsForVehicle($vehicleId, $startDate, $mysteryShopperHelper, $mysteryShopperSiteIds);

            $this->switchToHistory();

            $historyTests = parent::findTestsForVehicle($vehicleId, $startDate, $mysteryShopperHelper, $mysteryShopperSiteIds);

            return array_merge($currentTests, $historyTests);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findTestsExcludingNonAuthoritativeTestsForVehicle($vehicleId, $startDate)
    {
        try {
            $currentTests = parent::findTestsExcludingNonAuthoritativeTestsForVehicle($vehicleId, $startDate);

            $this->switchToHistory();

            $historyTests = parent::findTestsExcludingNonAuthoritativeTestsForVehicle($vehicleId, $startDate);

            return array_merge($currentTests, $historyTests);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findTestByVehicleRegistrationAndTestNumber($registration, $testNumber)
    {
        try {
            return parent::findTestByVehicleRegistrationAndTestNumber($registration, $testNumber);
        } catch (NoResultException $e) {
            $this->switchToHistory();

            return parent::findTestByVehicleRegistrationAndTestNumber($registration, $testNumber);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTest($id)
    {
        try {
            return parent::getMotTest($id);
        } catch (NotFoundException $e) {
            $this->switchToHistory();

            return parent::getMotTest($id);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestByNumber($motTestNumber)
    {
        try {
            return parent::getMotTestByNumber($motTestNumber);
        } catch (NotFoundException $e) {
            $this->switchToHistory();

            return parent::getMotTestByNumber($motTestNumber);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestLogsResult(MotTestSearchParam $searchParam)
    {
        $qb = $this->prepareMotTestLogResultQuery($searchParam);
        $sql = $qb->getSql();

        if ($searchParam->getDateFrom()) {
            $historyDate = new DateTime();
            $historyDate->sub(new \DateInterval('P4Y'));

            if ($searchParam->getDateFrom() < $historyDate) {

                $tableName = $this->getClassMetadata()->getTableName();

                $sql = sprintf(
                    '(%s) UNION (%s)',
                    $sql,
                    str_replace($tableName, str_replace(self::SUFFIX_CURRENT, self::SUFFIX_HISTORY, $tableName), $sql)
                );
            }
        }

        if ($searchParam->getFormat() !== SearchParamConst::FORMAT_DATA_CSV) {
            $orderBy = $searchParam->getSortColumnNameDatabase();
            if (!empty($orderBy)) {
                if (!is_array($orderBy)) {
                    $orderBy = [$orderBy];
                }

                $sql.= ' ORDER BY ';
                $sql.= implode(', ', array_map(function ($order) use ($searchParam) {
                    return sprintf('%s %s', $order, $searchParam->getSortDirection());
                }, $orderBy));
            }
        }
        if ($searchParam->getRowCount() > 0) {
            $sql.= sprintf(' LIMIT %d', $searchParam->getRowCount());
            if ($searchParam->getStart() > 0) {
                $sql.= sprintf(' OFFSET %d', $searchParam->getStart());
            }
        }

        $statement = $this->getEntityManager()->getConnection()->prepare($sql);
        $qb->bindParametersToStatement($statement);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestLogsResultCount(MotTestSearchParam $searchParam)
    {
        try {
            $count = parent::getMotTestLogsResultCount($searchParam);

            // Work out based on the search dates if the
            // mot_test_history needs to be counted.
            $historyDate = new DateTime();
            $historyDate->sub(new \DateInterval('P4Y'));

            if ($searchParam->getDateFrom()) {
                if ($searchParam->getDateFrom() < $historyDate) {
                    $this->switchToHistory();

                    $count['count'] += parent::getMotTestLogsResultCount($searchParam)['count'];
                }
            }

            return $count;
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOdometerHistoryForVehicleId($vehicleId, DateTime $dateTo = null, array $optionalMotTestTypeCodes = null, $limit = 4)
    {
        try {
            $current = parent::getOdometerHistoryForVehicleId($vehicleId, $dateTo, $optionalMotTestTypeCodes, $limit);

            if (count($current) >= $limit) {
                return $current;
            }

            $this->switchToHistory();

            $history = parent::getOdometerHistoryForVehicleId($vehicleId, $dateTo, $optionalMotTestTypeCodes, $limit - count($current));

            return array_merge($current, $history);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * Adds an additional condition to the query builder so that history DQL gets a different hash to the current one.
     * Relies on the assumption that repository methods only add where conditions instead of overriding them
     * (in short queries should be using andWhere() instead of where()).
     *
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias, $indexBy = null)
    {
        if (strpos($this->getClassMetadata()->getTableName(), self::SUFFIX_HISTORY)) {
            return parent::createQueryBuilder($alias, $indexBy)->andWhere('1=1');
        }

        return parent::createQueryBuilder($alias, $indexBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestSearchResult(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        return (new MotTestSearchQuery($this->getEntityManager(), $this->getClassMetadata()->getTableName()))
            ->getResult($searchParam, $optionalMotTestTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestSearchResultCount(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        return (new MotTestSearchQuery($this->getEntityManager(), $this->getClassMetadata()->getTableName()))
            ->countResult($searchParam, $optionalMotTestTypes);
    }

    public function switchToHistory()
    {
        $this->getClassMetadata()->setTableName(
            str_replace(self::SUFFIX_CURRENT, self::SUFFIX_HISTORY, $this->getClassMetadata()->getTableName())
        );
        $this->_em->getClassMetadata(MotTestReasonForRejection::class)->setTableName(
            str_replace(self::SUFFIX_CURRENT, self::SUFFIX_HISTORY, $this->_em->getClassMetadata(MotTestReasonForRejection::class)->getTableName())
        );
    }

    public function switchToCurrent()
    {
        $this->getClassMetadata()->setTableName(
            str_replace(self::SUFFIX_HISTORY, self::SUFFIX_CURRENT, $this->getClassMetadata()->getTableName())
        );
        $this->_em->getClassMetadata(MotTestReasonForRejection::class)->setTableName(
            str_replace(self::SUFFIX_HISTORY, self::SUFFIX_CURRENT, $this->_em->getClassMetadata(MotTestReasonForRejection::class)->getTableName())
        );
    }

    /**
     * @return string
     */
    protected function getVehicleIndexName()
    {
        if ($this->isOnCurrent()) {
            return parent::getVehicleIndexName();
        }

        return 'fk_mot_test_history_vehicle';
    }

    /**
     * @return bool
     */
    private function isOnCurrent()
    {
        return false !== strpos($this->getClassMetadata()->getTableName(), self::SUFFIX_CURRENT);
    }
}
