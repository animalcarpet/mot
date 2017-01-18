<?php

namespace DvsaEntities\Repository;

use DateTime;
use Doctrine\ORM\NoResultException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\MotTestReasonForRejection;
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
    )
    {
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
        $searchCurrent = true;
        $searchHistory = false;

        try {

            // Work out based on the search dates what mot_test tables
            // need to be searched.
            $historyDate = new DateTime();
            $historyDate->sub(new \DateInterval('P4Y'));

            if ($searchParam->getDateFrom())
            {
                if ($searchParam->getDateFrom() < $historyDate)
                {
                    $searchHistory = true;
                }
            }

            if ($searchParam->getDateTo())
            {
                if ($searchParam->getDateTo() < $historyDate)
                {
                    $searchCurrent = false;
                }
            }
            $current = [];
            if ($searchCurrent) {
                $current = parent::getMotTestLogsResult($searchParam);
            }
            $history = [];
            if ($searchHistory) {
                $this->switchToHistory();
                $history = parent::getMotTestLogsResult($searchParam);
            }

            return array_merge($current, $history);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestLogsResultCount(MotTestSearchParam $searchParam)
    {
        $searchCurrent = true;
        $searchHistory = false;

        try {

            // Work out based on the search dates what mot_test tables
            // need to be searched.
            $historyDate = new DateTime();
            $historyDate->sub(new \DateInterval('P4Y'));

            if ($searchParam->getDateFrom())
            {
                if ($searchParam->getDateFrom() < $historyDate)
                {
                    $searchHistory = true;
                }
            }

            if ($searchParam->getDateTo())
            {
                if ($searchParam->getDateTo() < $historyDate)
                {
                    $searchCurrent = false;
                }
            }

            $count = ['count' => 0];

            if ($searchCurrent) {
                $count = parent::getMotTestLogsResultCount($searchParam);
            }

            if ($searchHistory) {
                $this->switchToHistory();
                $count['count'] += parent::getMotTestLogsResultCount($searchParam)['count'];
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
        try {
            $current = parent::getMotTestSearchResult($searchParam, $optionalMotTestTypes);

            if ($searchParam->getRowCount() > 0 && count($current) >= $searchParam->getRowCount()) {
                return $current;
            }

            $this->switchToHistory();

            $history = parent::getMotTestSearchResult($searchParam, $optionalMotTestTypes);

            return array_merge($current, $history);
        } finally {
            $this->switchToCurrent();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMotTestSearchResultCount(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        try {
            $current = parent::getMotTestSearchResultCount($searchParam, $optionalMotTestTypes);

            $this->switchToHistory();

            $history = parent::getMotTestSearchResultCount($searchParam, $optionalMotTestTypes);

            return $current + $history;
        } finally {
            $this->switchToCurrent();
        }

    }

    private function switchToHistory()
    {
        $this->getClassMetadata()->setTableName(
            str_replace(self::SUFFIX_CURRENT, self::SUFFIX_HISTORY, $this->getClassMetadata()->getTableName())
        );
        $this->_em->getClassMetadata(MotTestReasonForRejection::class)->setTableName(
            str_replace(self::SUFFIX_CURRENT, self::SUFFIX_HISTORY, $this->_em->getClassMetadata(MotTestReasonForRejection::class)->getTableName())
        );
    }

    private function switchToCurrent()
    {
        $this->getClassMetadata()->setTableName(
            str_replace(self::SUFFIX_HISTORY, self::SUFFIX_CURRENT, $this->getClassMetadata()->getTableName())
        );
        $this->_em->getClassMetadata(MotTestReasonForRejection::class)->setTableName(
            str_replace(self::SUFFIX_HISTORY, self::SUFFIX_CURRENT, $this->_em->getClassMetadata(MotTestReasonForRejection::class)->getTableName())
        );
    }
}
