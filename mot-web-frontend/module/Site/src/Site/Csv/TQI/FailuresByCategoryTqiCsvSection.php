<?php
namespace Site\Csv\TQI;

use Core\Formatting\ComponentFailRateFormatter;
use Core\Formatting\FailedTestsPercentageFormatter;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;

class FailuresByCategoryTqiCsvSection
{
    /** @var NationalComponentStatisticsDto */
    private $nationalComponentStatisticsDto;
    /** @var SiteComponentStatisticsDto */
    private $siteComponentStatisticsDto;
    /** @var ComponentBreakdownDto[] */
    private $componentBreakdownForTesters;
    /** @var FailedTestsPercentageFormatter */
    private $percentageFormatter;
    /** @var int */
    private $rowsNumber;
    /** @var int */
    private $colsNumber;

    const SITE_AVERAGE_COLUMN_NAME = 'Site average';
    const NATIONAL_AVERAGE_COLUMN_NAME = 'National average';
    const HEADERS_COLUMN = 0;
    const SECTION_TITLE_ROW = 0;

    /**
     * FailuresByCategoryTqiCsvSection constructor.
     * @param NationalComponentStatisticsDto $nationalComponentStatisticsDto
     * @param SiteComponentStatisticsDto $siteComponentStatisticsDto
     * @param ComponentBreakdownDto[] $componentBreakdownForTesters
     * @param int $colsNumber
     */
    public function __construct(
        NationalComponentStatisticsDto $nationalComponentStatisticsDto,
        SiteComponentStatisticsDto $siteComponentStatisticsDto,
        array $componentBreakdownForTesters,
        int $colsNumber
    )
    {
        $this->nationalComponentStatisticsDto = $nationalComponentStatisticsDto;
        $this->siteComponentStatisticsDto = $siteComponentStatisticsDto;
        $this->componentBreakdownForTesters = $componentBreakdownForTesters;
        $this->percentageFormatter = new FailedTestsPercentageFormatter();
        $this->colsNumber = $colsNumber;
        $this->rowsNumber = count($this->siteComponentStatisticsDto->getComponents()) + 1;
    }

    public function build()
    {
        $siteComponents = $this->siteComponentStatisticsDto->getComponents();

        $csv = new Csv($this->rowsNumber, $this->colsNumber);
        $csv->addField(self::SECTION_TITLE_ROW, self::HEADERS_COLUMN, "Failures by category");

        $column = 1;
        $rows = 1;
        foreach ($siteComponents as $key => $component) {
            //component name
            $csv->addField($rows, self::HEADERS_COLUMN, $component->getName());
            //testers fail rates
            foreach ($this->componentBreakdownForTesters as $testerComponentBreakdown) {
                $testerComponent = $this->findComponentById($component->getId(), $testerComponentBreakdown->getComponents());
                $csv->addField($rows, $column, ComponentFailRateFormatter::format($testerComponent->getPercentageFailed()) . '%');
                $column++;
            }
            //site average
            $csv->addField($rows, $column, ComponentFailRateFormatter::format($component->getPercentageFailed()) . '%');
            //national average
            $column++;
            $nationalComponent = $this->findComponentById($component->getId(), $this->nationalComponentStatisticsDto->getComponents());
            $csv->addField($rows, $column, ComponentFailRateFormatter::format($nationalComponent->getPercentageFailed()) . '%');

            $column = 1;
            $rows++;
        }

        return $csv->toArray();
    }

    /**
     * @param int $id
     * @param ComponentDto[] $components
     * @return ComponentDto
     */
    private function findComponentById(int $id, array $components = null): ComponentDto
    {
        foreach ($components as $component) {
            if ($component->getId() === $id) {
                return $component;
            }
        }

        return (new ComponentDto())->setPercentageFailed(0);
    }
}