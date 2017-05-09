<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="enforcement_mot_test_differences", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 */
class EnforcementMotTestDifference extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\EnforcementMotTestResult
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementMotTestResult", inversedBy="testDifferences")
     * @ORM\JoinColumn(name="enforcement_mot_test_result_id", referencedColumnName="id")
     */
    protected $motTestResult;

    /**
     * @var \DvsaEntities\Entity\ReasonForRejection
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\ReasonForRejection")
     * @ORM\JoinColumn(name="rfr_id", referencedColumnName="id")
     */
    protected $rfr;

    /**
     * @var \DvsaEntities\Entity\MotTest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTest")
     * @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     */
    protected $motTest;

    /**
     * @var \DvsaEntities\Entity\MotTestReasonForRejection
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTestReasonForRejection")
     * @ORM\JoinColumn(name="mot_test_rfr_map_id", referencedColumnName="id")
     */
    protected $motTestRfr;

    /**
     * @var \DvsaEntities\Entity\MotTestType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTestType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_type_id", referencedColumnName="id")
     * })
     */
    protected $motTestType;

    /**
     * @var \DvsaEntities\Entity\EnforcementDecisionScore
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementDecisionScore")
     * @ORM\JoinColumn(name="enforcement_decision_score_lookup_id", referencedColumnName="id")
     */
    protected $score;

    /**
     * @var \DvsaEntities\Entity\EnforcementDecision
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementDecision")
     * @ORM\JoinColumn(name="enforcement_decision_lookup_id", referencedColumnName="id")
     */
    protected $decision;

    /**
     * @var \DvsaEntities\Entity\EnforcementDecisionCategory
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementDecisionCategory")
     * @ORM\JoinColumn(name="enforcement_decision_category_lookup_id", referencedColumnName="id")
     */
    protected $decisionCategory;

    /**
     * @var \DvsaEntities\Entity\Comment
     *
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id")
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Comment", cascade={"PERSIST"})
     */
    protected $comment;

    /**
     * Fire it up..
     */
    public function __construct()
    {
        $this->setScore(0);
    }

    /**
     * @param \DvsaEntities\Entity\Comment $comment
     *
     * @return EnforcementMotTestDifference
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \DvsaEntities\Entity\EnforcementDecision $decision
     *
     * @return EnforcementMotTestDifference
     */
    public function setDecision($decision)
    {
        $this->decision = $decision;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementDecision
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * @param \DvsaEntities\Entity\EnforcementDecisionCategory $decisionCategory
     *
     * @return EnforcementMotTestDifference
     */
    public function setDecisionCategory($decisionCategory)
    {
        $this->decisionCategory = $decisionCategory;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementDecisionCategory
     */
    public function getDecisionCategory()
    {
        return $this->decisionCategory;
    }

    /**
     * @param \DvsaEntities\Entity\MotTest $motTest
     *
     * @return EnforcementMotTestDifference
     */
    public function setMotTest($motTest)
    {
        $this->motTest = $motTest;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTest
     */
    public function getMotTest()
    {
        return $this->motTest;
    }

    /**
     * @param \DvsaEntities\Entity\EnforcementMotTestResult $motTestResult
     *
     * @return EnforcementMotTestDifference
     */
    public function setMotTestResult($motTestResult)
    {
        $this->motTestResult = $motTestResult;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementMotTestResult
     */
    public function getMotTestResult()
    {
        return $this->motTestResult;
    }

    /**
     * @param \DvsaEntities\Entity\MotTestReasonForRejection $motTestRfr
     *
     * @return EnforcementMotTestDifference
     */
    public function setMotTestRfr($motTestRfr)
    {
        $this->motTestRfr = $motTestRfr;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTestReasonForRejection
     */
    public function getMotTestRfr()
    {
        return $this->motTestRfr;
    }

    /**
     * @param \DvsaEntities\Entity\MotTestType $motTestType
     *
     * @return EnforcementMotTestDifference
     */
    public function setMotTestType(MotTestType $motTestType)
    {
        $this->motTestType = $motTestType;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTestType
     */
    public function getMotTestType()
    {
        return $this->motTestType;
    }

    /**
     * @param \DvsaEntities\Entity\ReasonForRejection $rfr
     *
     * @return EnforcementMotTestDifference
     */
    public function setRfr($rfr)
    {
        $this->rfr = $rfr;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\ReasonForRejection
     */
    public function getRfr()
    {
        return $this->rfr;
    }

    /**
     * @param \DvsaEntities\Entity\EnforcementDecisionScore $score
     *
     * @return EnforcementMotTestDifference
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementDecisionScore
     */
    public function getScore()
    {
        return $this->score;
    }
}
