<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel\Survey;

use DateTimeImmutable;

/**
 * Representation of a GDS Satisfaction Survey report with results for a single month.
 */
class DownloadableSurveyReport
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $csvData;

    /**
     * DownloadableSurveyReport constructor.
     *
     * @param DateTimeImmutable $date
     * @param int               $size
     * @param string            $csvData
     */
    public function __construct(DateTimeImmutable $date, $size, $csvData)
    {
        $this->date = $date;
        $this->size = $size;
        $this->csvData = $csvData;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getFormattedDate($format)
    {
        return $this->date->format($format);
    }

    /**
     * @return float
     */
    public function getSizeInKilobytes()
    {
        return round($this->size / 1024, 2);
    }

    /**
     * @return string
     */
    public function getCsvData()
    {
        return $this->csvData;
    }
}
