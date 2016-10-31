<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel\Survey;

use ArrayIterator;
use DateTimeImmutable;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * GDS Satisfaction Survey reports, grouped by year and month.
 */
class DownloadableSurveyReports implements IteratorAggregate
{
    /**
     * @var array
     */
    private $reports;

    /**
     * DownloadableSurveyReports constructor.
     *
     * @param DownloadableSurveyReport[] $reports
     */
    public function __construct(array $reports)
    {
        $this->reports = $reports;
    }

    /**
     * @param array $result
     *
     * @return DownloadableSurveyReports
     */
    public static function fromApi(array $result)
    {
        if (!isset($result['data'])) {
            return new DownloadableSurveyReports([]);
        }

        $reports = [];
        $count = 0;
        foreach ($result['data'] as $data) {
            if (!isset($data['month']) || !isset($data['size']) || !isset($data['csv'])) {
                continue;
            }

            if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $data['month'])) {
                continue;
            }

            $date = DateTimeImmutable::createFromFormat('!Y-m', $data['month']);
            $year = $date->format('Y');
            if (!isset($reports[$year])) {
                $reports[$year] = [];
            }

            $reports[$year][$date->format('m')] = new DownloadableSurveyReport($date, $data['size'], $data['csv']);
            $count++;

            if ($count >= 12) {
                break;
            }
        }

        return new DownloadableSurveyReports($reports);
    }

    /**
     * $year and $month must be in the 'Y', 'm' format.
     *
     * @param string $year
     * @param string $month
     *
     * @throws OutOfBoundsException
     *
     * @return DownloadableSurveyReport
     */
    public function getReport($year, $month)
    {
        if (!isset($this->reports[$year][$month])) {
            throw new OutOfBoundsException(sprintf('[GDS Satisfaction Survey] Requested report for year "%s" and month "%s" does not exist.',
                $year, $month));
        }

        return $this->reports[$year][$month];
    }

    /**
     * Retrieve an external iterator.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->reports);
    }
}
