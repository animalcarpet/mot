<?php

namespace DvsaMotTest\Adapter;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;

/**
 * An adapter class which allows modification of the response from mot-test-service.
 *
 * This class was added after the introduction of the mot-test-service significantly replaced the use of mot-api and an MotTestDto object in the front-end. This class re-implements the logic originally contained in the MotTestDto, for use in the limited number of views which require this logic (i.e. filtering out of repaired defects, and handling when issue date is null).
 */
class MotTestServiceResponseAdapter
{
    /** @var MotTest $motTest */
    protected $motTest;

    /**
     * @param MotTest $motTest
     */
    public function __construct(MotTest $motTest)
    {
        $this->motTest = $motTest;
    }

    /**
     * @return string
     */
    public function getIssuedDate()
    {
        if ($this->motTest->getIssuedDate()) {
            return $this->motTest->getIssuedDate();
        }
        if ($this->motTest->getCompletedDate()) {
            return $this->motTest->getCompletedDate();
        }

        return $this->motTest->getStartedDate();
    }

    /**
     * @return \stdClass
     */
    public function getReasonsForRejectionExcludingRepairedDefects()
    {
        $reasonsForRejection = [];
        if (isset($this->motTest)) {
            $reasonsForRejection = $this->motTest->getReasonsForRejection();
        }
        $result = new \stdClass();

        foreach (['FAIL', 'PRS', 'ADVISORY'] as $type) {
            $defects = isset($reasonsForRejection->$type) ? $reasonsForRejection->$type : [];
            $result->$type = $this->removeRepairedDefectsByType($defects);
        }

        return $result;
    }

    /**
     * @param $defects
     * @param $result
     *
     * @return mixed
     */
    private function removeRepairedDefectsByType($defects)
    {
        return array_filter($defects, function ($defect) {
            return !isset($defect->markedAsRepaired) || true !== $defect->markedAsRepaired;
        });
    }
}
