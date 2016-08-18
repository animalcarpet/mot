<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Report;

use Dvsa\Mot\Api\StatisticsApi\ReportGeneration\AbstractReportGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Repository\NationalComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use DvsaCommon\ApiClient\Statistics\Common\ReportDtoInterface;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class NationalComponentStatisticsReportGenerator extends AbstractReportGenerator
{
    private $repository;
    private $storage;
    private $year;
    private $month;
    private $group;

    public function __construct(
        NationalComponentFailRateStorage $storage,
        NationalComponentStatisticsRepository $componentStatisticsRepository,
        DateTimeHolderInterface $dateTimeHolder,
        TimeSpan $timeoutPeriod,
        $year,
        $month,
        $group
    )
    {
        parent::__construct($dateTimeHolder, $timeoutPeriod);

        $this->repository = $componentStatisticsRepository;
        $this->storage = $storage;
        $this->year = $year;
        $this->month = $month;
        $this->group = $group;
    }

    /**
     * @return ReportDtoInterface
     */
    protected function generateReport()
    {
        $total = $this->repository->getNationalFailedMotTestCount($this->group, $this->year, $this->month);
        $results = $this->repository->get($this->group, $this->year, $this->month);
        $report = $this->buildComponentDtosFromQueryResults($results, $total);
        $report->setYear($this->year);
        $report->setMonth($this->month);
        $report->setGroup($this->group);

        return $report;
    }

    protected function storeReport($report)
    {
        $this->storage->store($this->year, $this->month, $this->group, $report);
    }

    /**
     * @return ReportDtoInterface
     */
    function createEmptyReport()
    {
        return new NationalComponentStatisticsDto();
    }

    /**
     * @return ReportDtoInterface
     */
    protected function getFromStorage()
    {
        return $this->storage->get($this->year, $this->month, $this->group);
    }

    /**
     * @param $results \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult[]
     * @param $total
     * @return NationalComponentStatisticsDto
     */
    private function buildComponentDtosFromQueryResults(array $results, $total)
    {
        $componentDtos = [];
        foreach ($results as $result) {
            $componentDto = new ComponentDto();
            $componentDto
                ->setPercentageFailed($total > 0 ? 100 * $result->getFailedCount() / $total : 0)
                ->setName($result->getTestItemCategoryName())
                ->setId($result->getTestItemCategoryId());

            $componentDtos[] = $componentDto;
        }

        return (new NationalComponentStatisticsDto())->setComponents($componentDtos);
    }

    /**
     * @return ReflectiveDtoInterface
     */
    protected function returnInProgressReportDto()
    {
        $dto = new NationalComponentStatisticsDto();
        $dto->getReportStatus()->setIsCompleted(false);

        return $dto;
    }
}
