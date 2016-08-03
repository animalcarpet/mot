<?php

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use DvsaCommon\Dto\Common\MotTestDto;

/**
 * Contains the information required for the list of observed defects in an MOT
 * test.
 */
class ObservedDefectCollection
{
    const FAILURE = 'FAIL';
    const PRS = 'PRS';
    const ADVISORY = 'ADVISORY';
    
    /**
     * @var ObservedDefect[]
     */
    private $failures;

    /**
     * @var ObservedDefect[]
     */
    private $prs;

    /**
     * @var ObservedDefect[]
     */
    private $advisories;

    /**
     * ObservedDefectCollection constructor.
     *
     * @param ObservedDefect[] $failures
     * @param ObservedDefect[] $prs
     * @param ObservedDefect[] $advisories
     */
    private function __construct(array $failures, array $prs, array $advisories)
    {
        $this->failures = $failures;
        $this->prs = $prs;
        $this->advisories = $advisories;
    }

    /**
     * @param MotTestDto $motTestData
     *
     * @return ObservedDefectCollection
     */
    public static function fromMotApiData(MotTestDto $motTestData)
    {
        $failuresFromApi = isset($motTestData->getReasonsForRejection()[self::FAILURE])
            ? $motTestData->getReasonsForRejection()[self::FAILURE]
            : [];

        $prsFromApi = isset($motTestData->getReasonsForRejection()[self::PRS])
            ? $motTestData->getReasonsForRejection()[self::PRS]
            : [];

        $advisoriesFromApi = isset($motTestData->getReasonsForRejection()[self::ADVISORY])
            ? $motTestData->getReasonsForRejection()[self::ADVISORY]
            : [];

        $failures = [];
        $prs = [];
        $advisories = [];

        foreach ($failuresFromApi as $failure) {
            $observedDefect = new ObservedDefect(
                self::FAILURE,
                $failure['locationLateral'],
                $failure['locationLongitudinal'],
                $failure['locationVertical'],
                $failure['comment'],
                $failure['failureDangerous'],
                $failure['testItemSelectorDescription'].' '.$failure['failureText']
            );

            array_push($failures, $observedDefect);
        }

        foreach ($prsFromApi as $loopPrs) {
            $observedDefect = new ObservedDefect(
                self::PRS,
                $loopPrs['locationLateral'],
                $loopPrs['locationLongitudinal'],
                $loopPrs['locationVertical'],
                $loopPrs['comment'],
                $loopPrs['failureDangerous'],
                $loopPrs['testItemSelectorDescription'].' '.$loopPrs['failureText']
            );

            array_push($prs, $observedDefect);
        }

        foreach ($advisoriesFromApi as $advisory) {
            $observedDefect = new ObservedDefect(
                self::ADVISORY,
                $advisory['locationLateral'],
                $advisory['locationLongitudinal'],
                $advisory['locationVertical'],
                $advisory['comment'],
                $advisory['failureDangerous'],
                $advisory['testItemSelectorDescription'].' '.$advisory['failureText']
            );

            array_push($advisories, $observedDefect);
        }

        return new self($failures, $prs, $advisories);
    }

    /**
     * @return ObservedDefect[]
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * @return ObservedDefect[]
     */
    public function getPrs()
    {
        return $this->prs;
    }

    /**
     * @return ObservedDefect[]
     */
    public function getAdvisories()
    {
        return $this->advisories;
    }

    /**
     * @return int
     */
    public function getNumberOfFailures()
    {
        return count($this->failures);
    }

    /**
     * @return int
     */
    public function getNumberOfPrs()
    {
        return count($this->prs);
    }

    /**
     * @return int
     */
    public function getNumberOfAdvisories()
    {
        return count($this->advisories);
    }

    /**
     * @return bool
     */
    public function hasFailuresPrsOrAdvisories()
    {
        return !(empty($this->failures) && empty($this->prs) && empty($this->advisories));
    }
}
