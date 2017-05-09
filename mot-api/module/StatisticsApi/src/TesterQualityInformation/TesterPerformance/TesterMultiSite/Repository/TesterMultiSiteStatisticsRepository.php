<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\QueryBuilder\TesterMultiSiteStatisticsQueryBuilder;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\QueryResult\TesterMultiSitePerformanceResult;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\Address;

class TesterMultiSiteStatisticsRepository extends TesterManyGroupsStatisticsRepository implements AutoWireableInterface
{
    protected function getSql()
    {
        return (new TesterMultiSiteStatisticsQueryBuilder())->getSql();
    }

    protected function buildResultSetMapping()
    {
        $rsm = parent::buildResultSetMapping();

        $rsm->addScalarResult('siteId', 'siteId')
            ->addScalarResult('siteName', 'siteName')
            ->addScalarResult('siteAddressLine1', 'siteAddressLine1')
            ->addScalarResult('siteAddressLine2', 'siteAddressLine2')
            ->addScalarResult('siteAddressLine3', 'siteAddressLine3')
            ->addScalarResult('siteAddressLine4', 'siteAddressLine4')
            ->addScalarResult('sitePostcode', 'sitePostcode')
            ->addScalarResult('siteTown', 'siteTown')
            ->addScalarResult('siteCountry', 'siteCountry');

        return $rsm;
    }

    protected function createResultRow($row)
    {
        $address = new Address();
        $address->setAddressLine1($row['siteAddressLine1'])
            ->setAddressLine2($row['siteAddressLine2'])
            ->setAddressLine3($row['siteAddressLine3'])
            ->setAddressLine4($row['siteAddressLine4'])
            ->setTown($row['siteTown'])
            ->setCountry($row['siteCountry'])
            ->setPostcode($row['sitePostcode']);

        $testerPerformanceResult = new TesterMultiSitePerformanceResult();

        $testerPerformanceResult->setVehicleClassGroup($row['vehicleClassGroup'])
            ->setTotalTime($row['totalTime'])
            ->setAverageVehicleAgeInMonths((float) $row['averageVehicleAgeInMonths'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setFailedCount($row['failedCount'])
            ->setTotalCount($row ['totalCount'])
            ->setSiteId((int) $row['siteId'])
            ->setSiteName($row['siteName'])
            ->setSiteAddress($address);

        return $testerPerformanceResult;
    }
}
