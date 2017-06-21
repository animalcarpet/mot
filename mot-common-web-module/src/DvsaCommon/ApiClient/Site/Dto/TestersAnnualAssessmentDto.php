<?php


namespace DvsaCommon\ApiClient\Site\Dto;


use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class TestersAnnualAssessmentDto implements ReflectiveDtoInterface
{
    /** @var  GroupAssessmentListItem[] */
    private $groupAAssessments;

    /** @var  GroupAssessmentListItem[] */
    private $groupBAssessments;

    /**
     * @return GroupAssessmentListItem[]
     */
    public function getGroupAAssessments()
    {
        return $this->groupAAssessments;
    }

    /**
     * @param \DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem[] $groupAAssessments
     * @return TestersAnnualAssessmentDto
     */
    public function setGroupAAssessments($groupAAssessments)
    {
        $this->groupAAssessments = $groupAAssessments;
        return $this;
    }

    /**
     * @return \DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem[]
     */
    public function getGroupBAssessments()
    {
        return $this->groupBAssessments;
    }

    /**
     * @param \DvsaCommon\ApiClient\Site\Dto\GroupAssessmentListItem[] $groupBAssessments
     * @return TestersAnnualAssessmentDto
     */
    public function setGroupBAssessments($groupBAssessments)
    {
        $this->groupBAssessments = $groupBAssessments;
        return $this;
    }
}