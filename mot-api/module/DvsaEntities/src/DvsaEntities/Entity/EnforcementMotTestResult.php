<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="enforcement_mot_test_result", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 */
class EnforcementMotTestResult extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\MotTest This is the re-inspection mot test id
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\MotTest", fetch="EAGER")
     * @ORM\JoinColumn(name="re_inspection_mot_test_id", referencedColumnName="id")
     */
    protected $motTestInspection;

    /**
     * @var \DvsaEntities\Entity\MotTest This is the mot test id
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\MotTest", fetch="EAGER")
     * @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     */
    protected $motTest;

    /**
     * @var int
     *
     * @ORM\Column(name="total_score", type="smallint", nullable=false)
     */
    protected $totalScore;

    /**
     * @var \DvsaEntities\Entity\EnforcementDecisionOutcome
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementDecisionOutcome", fetch="EAGER")
     * @ORM\JoinColumn(name="enforcement_decision_outcome_lookup_id", referencedColumnName="id")
     */
    protected $decisionOutcome;

    /**
     * @var \DvsaEntities\Entity\Comment
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Comment", cascade={"PERSIST"})
     */
    protected $comment;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EnforcementMotTestDifference", mappedBy="motTestResult")
     */
    protected $testDifferences;

    /**
     * @var \DvsaEntities\Entity\EnforcementDecisionReinspectionOutcome
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EnforcementDecisionReinspectionOutcome", fetch="EAGER")
     * @ORM\JoinColumn(name="enforcement_decision_reinspection_outcome_lookup_id", referencedColumnName="id")
     */
    protected $decisionInspectionOutcome;

    /**
     * @var string Step
     *
     * @ORM\Column(type="string")
     */
    protected $step;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\EnforcementMotTestResultWitnesses",
     *     mappedBy="enforcementMotTestResult", fetch="EAGER", cascade={"persist"})
     */
    protected $enforcementMotTestResultWitnesses;

    /**
     * @ORM\Column(name="awl_advice_given", nullable=true, type="string")
     */
    protected $awlAdviceGiven;

    /**
     * @ORM\Column(name="awl_immediate_attention", nullable=true, type="string")
     */
    protected $awlImmediateAttention;

    /**
     * @ORM\Column(name="awl_reply_comments", nullable=true, type="string")
     */
    protected $awlReplyComments;

    /**
     * @ORM\Column(name="awl_name_a_ere", nullable=true, type="string")
     */
    protected $awlNameAEre;

    /**
     * @ORM\Column(name="awl_mot_roles", nullable=true, type="string")
     */
    protected $awlMotRoles;

    /**
     * @ORM\Column(name="awl_position_vts", nullable=true, type="string")
     */
    protected $awlPositionVts;

    /**
     * @ORM\Column(name="awl_user_id", nullable=true, type="string")
     */
    protected $awlUserId;

    /**
     * @ORM\Column(name="complainant_name", nullable=true, type="string")
     */
    protected $complaintName;

    /**
     * @ORM\Column(name="complaint_detail", nullable=true, type="string")
     */
    protected $complaintDetail;

    /**
     * @ORM\Column(name="repairs_detail", nullable=true, type="string")
     */
    protected $repairsDetail;

    /**
     * @ORM\Column(name="complainant_address", nullable=true, type="string")
     */
    protected $complainantAddress;

    /**
     * @ORM\Column(name="complainant_postcode", nullable=true, type="string")
     */
    protected $complainantPostcode;

    /**
     * @ORM\Column(name="complainant_phone_number", nullable=true, type="string")
     */
    protected $complainantPhoneNumber;

    /**
     * @ORM\Column(name="ve_completed", nullable=true, type="string")
     */
    protected $veCompleted;

    /**
     * @ORM\Column(name="agree_vehicle_to_certificate", nullable=true, type="string")
     */
    protected $agreeVehicleToCertificate;

    /**
     * @ORM\Column(name="input_agree_vehicle_to_certificate", nullable=true, type="string")
     */
    protected $inputAgreeVehicleToCertificate;

    /**
     * @ORM\Column(name="agree_vehicle_to_fail", nullable=true, type="string")
     */
    protected $agreeVehicleToFail;

    /**
     * @ORM\Column(name="input_agree_vehicle_to_fail", nullable=true, type="string")
     */
    protected $inputAgreeVehicleToFail;

    /**
     * @ORM\Column(name="vehicle_switch", nullable=true, type="string")
     */
    protected $vehicleSwitch;

    /**
     * @ORM\Column(name="input_vehicle_switch", nullable=true, type="string")
     */
    protected $inputVehicleSwitch;

    /**
     * @ORM\Column(name="switch_police_status_report", nullable=true, type="string")
     */
    protected $switchPoliceStatusReport;

    /**
     * @ORM\Column(name="input_switch_detail_report", nullable=true, type="string")
     */
    protected $inputSwitchDetailReport;

    /**
     * @ORM\Column(name="switch_vehicle_result", nullable=true, type="string")
     */
    protected $switchVehicleResult;

    /**
     * @ORM\Column(name="input_switch_police_status_report", nullable=true, type="string")
     */
    protected $inputSwitchPoliceStatusReport;

    /**
     * @ORM\Column(name="promote_sale_interest", nullable=true, type="string")
     */
    protected $promoteSaleInterest;

    /**
     * @ORM\Column(name="input_promote_sale_interest", nullable=true, type="string")
     */
    protected $inputPromoteSaleInterest;

    /**
     * @ORM\Column(name="vehicle_defects", nullable=true, type="string")
     */
    protected $vehicleDefects;

    /**
     * @ORM\Column(name="reason_of_defects", nullable=true, type="string")
     */
    protected $reasonOfDefects;

    /**
     * @ORM\Column(name="items_discussed", nullable=true, type="string")
     */
    protected $itemsDiscussed;

    /**
     * @ORM\Column(name="concluding_remarks_tester", nullable=true, type="string")
     */
    protected $concludingRemarksTester;

    /**
     * @ORM\Column(name="concluding_remarks_ae", nullable=true, type="string")
     */
    protected $concludingRemarksAe;

    /**
     * @ORM\Column(name="concluding_remarks_recommendation", nullable=true, type="string")
     */
    protected $concludingRemarksRecommendation;

    /**
     * @ORM\Column(name="concluding_remarks_name", nullable=true, type="string")
     */
    protected $concludingRemarksName;

    /**
     * Set up initial values in constructor.
     */
    public function __construct()
    {
        $this->testDifferences = new ArrayCollection();
        $this->enforcementMotTestResultWitnesses = new ArrayCollection();
    }

    /**
     * @param \DvsaEntities\Entity\Comment $comment
     *
     * @return EnforcementMotTestResult
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
     * @param \DvsaEntities\Entity\EnforcementDecisionOutcome $decisionOutcome
     *
     * @return EnforcementMotTestResult
     */
    public function setDecisionOutcome($decisionOutcome)
    {
        $this->decisionOutcome = $decisionOutcome;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementDecisionOutcome
     */
    public function getDecisionOutcome()
    {
        return $this->decisionOutcome;
    }

    /**
     * @param int $totalScore
     *
     * @return EnforcementMotTestResult
     */
    public function setTotalScore($totalScore)
    {
        $this->totalScore = $totalScore;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalScore()
    {
        return $this->totalScore;
    }

    /**
     * @param \DvsaEntities\Entity\MotTest $motTest
     *
     * @return EnforcementMotTestResult
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTestDifferences()
    {
        return $this->testDifferences;
    }

    /**
     * @param \DvsaEntities\Entity\MotTest $motTestInspection
     *
     * @return EnforcementMotTestResult
     */
    public function setMotTestInspection($motTestInspection)
    {
        $this->motTestInspection = $motTestInspection;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTest
     */
    public function getMotTestInspection()
    {
        return $this->motTestInspection;
    }

    /**
     * @param \DvsaEntities\Entity\EnforcementDecisionInspectionOutcome $decisionInspectionOutcome
     *
     * @return EnforcementMotTestResult
     */
    public function setDecisionInspectionOutcome($decisionInspectionOutcome)
    {
        $this->decisionInspectionOutcome = $decisionInspectionOutcome;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\EnforcementDecisionInspectionOutcome
     */
    public function getDecisionInspectionOutcome()
    {
        return $this->decisionInspectionOutcome;
    }

    /**
     * @return the string
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param
     *            $step
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEnforcementMotTestResultWitnesses()
    {
        return $this->enforcementMotTestResultWitnesses;
    }

    public function addEnforcementMotTestResultWitness($enforcementMotTestResultWitness)
    {
        $this->enforcementMotTestResultWitnesses->add($enforcementMotTestResultWitness);

        return $this;
    }

    public function addEnforcementMotTestResultWitnesses($enforcementMotTestResultWitnesses)
    {
        foreach ($enforcementMotTestResultWitnesses as $enforcementMotTestResultWitness) {
            $this->addEnforcementMotTestResultWitness($enforcementMotTestResultWitness);
        }

        return $this;
    }

    public function removeEnforcementMotTestResultWitnesses($enforcementMotTestResultWitnesses)
    {
        foreach ($enforcementMotTestResultWitnesses as $enforcementMotTestResultWitness) {
            $this->enforcementMotTestResultWitnesses->remove($enforcementMotTestResultWitness);
        }
    }

    /**
     * @return string|null
     */
    public function getAwlAdviceGiven()
    {
        return $this->awlAdviceGiven;
    }

    /**
     * @param string|null $awlAdviceGiven
     */
    public function setAwlAdviceGiven($awlAdviceGiven)
    {
        $this->awlAdviceGiven = $awlAdviceGiven;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwlImmediateAttention()
    {
        return $this->awlImmediateAttention;
    }

    /**
     * @param string|null $awlImmediateAttention
     */
    public function setAwlImmediateAttention($awlImmediateAttention)
    {
        $this->awlImmediateAttention = $awlImmediateAttention;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwlReplyComments()
    {
        return $this->awlReplyComments;
    }

    /**
     * @param string|null $awlReplyComments
     */
    public function setAwlReplyComments($awlReplyComments)
    {
        $this->awlReplyComments = $awlReplyComments;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwlNameAEre()
    {
        return $this->awlNameAEre;
    }

    /**
     * @param string|null $awlNameAEre
     */
    public function setAwlNameAEre($awlNameAEre)
    {
        $this->awlNameAEre = $awlNameAEre;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwlMotRoles()
    {
        return $this->awlMotRoles;
    }

    /**
     * @param string|null $awlMotRoles
     */
    public function setAwlMotRoles($awlMotRoles)
    {
        $this->awlMotRoles = $awlMotRoles;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwlPositionVts()
    {
        return $this->awlPositionVts;
    }

    /**
     * @param string|null $awlPositionVts
     */
    public function setAwlPositionVts($awlPositionVts)
    {
        $this->awlPositionVts = $awlPositionVts;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAwlUserId()
    {
        return $this->awlUserId;
    }

    /**
     * @param string|null $awlUserId
     */
    public function setAwlUserId($awlUserId)
    {
        $this->awlUserId = $awlUserId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplaintName()
    {
        return $this->complaintName;
    }

    /**
     * @param string|null $complaintName
     */
    public function setComplaintName($complaintName)
    {
        $this->complaintName = $complaintName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplaintDetail()
    {
        return $this->complaintDetail;
    }

    /**
     * @param string|null $complaintDetail
     */
    public function setComplaintDetail($complaintDetail)
    {
        $this->complaintDetail = $complaintDetail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRepairsDetail()
    {
        return $this->repairsDetail;
    }

    /**
     * @param string|null $repairsDetail
     */
    public function setRepairsDetail($repairsDetail)
    {
        $this->repairsDetail = $repairsDetail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplainantAddress()
    {
        return $this->complainantAddress;
    }

    /**
     * @param string|null $complainantAddress
     */
    public function setComplainantAddress($complainantAddress)
    {
        $this->complainantAddress = $complainantAddress;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplainantPostcode()
    {
        return $this->complainantPostcode;
    }

    /**
     * @param string|null $complainantPostcode
     */
    public function setComplainantPostcode($complainantPostcode)
    {
        $this->complainantPostcode = $complainantPostcode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplainantPhoneNumber()
    {
        return $this->complainantPhoneNumber;
    }

    /**
     * @param string|null $complainantPhoneNumber
     */
    public function setComplainantPhoneNumber($complainantPhoneNumber)
    {
        $this->complainantPhoneNumber = $complainantPhoneNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getVeCompleted()
    {
        return $this->veCompleted;
    }

    /**
     * @param string|null $veCompleted
     */
    public function setVeCompleted($veCompleted)
    {
        $this->veCompleted = $veCompleted;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAgreeVehicleToCertificate()
    {
        return $this->agreeVehicleToCertificate;
    }

    /**
     * @param string|null $agreeVehicleToCertificate
     */
    public function setAgreeVehicleToCertificate($agreeVehicleToCertificate)
    {
        $this->agreeVehicleToCertificate = $agreeVehicleToCertificate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputAgreeVehicleToCertificate()
    {
        return $this->inputAgreeVehicleToCertificate;
    }

    /**
     * @param string|null $inputAgreeVehicleToCertificate
     */
    public function setInputAgreeVehicleToCertificate($inputAgreeVehicleToCertificate)
    {
        $this->inputAgreeVehicleToCertificate = $inputAgreeVehicleToCertificate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAgreeVehicleToFail()
    {
        return $this->agreeVehicleToFail;
    }

    /**
     * @param string|null $agreeVehicleToFail
     */
    public function setAgreeVehicleToFail($agreeVehicleToFail)
    {
        $this->agreeVehicleToFail = $agreeVehicleToFail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputAgreeVehicleToFail()
    {
        return $this->inputAgreeVehicleToFail;
    }

    /**
     * @param string|null $inputAgreeVehicleToFail
     */
    public function setInputAgreeVehicleToFail($inputAgreeVehicleToFail)
    {
        $this->inputAgreeVehicleToFail = $inputAgreeVehicleToFail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getVehicleSwitch()
    {
        return $this->vehicleSwitch;
    }

    /**
     * @param string|null $vehicleSwitch
     */
    public function setVehicleSwitch($vehicleSwitch)
    {
        $this->vehicleSwitch = $vehicleSwitch;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputVehicleSwitch()
    {
        return $this->inputVehicleSwitch;
    }

    /**
     * @param string|null $inputVehicleSwitch
     */
    public function setInputVehicleSwitch($inputVehicleSwitch)
    {
        $this->inputVehicleSwitch = $inputVehicleSwitch;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSwitchPoliceStatusReport()
    {
        return $this->switchPoliceStatusReport;
    }

    /**
     * @param string|null $switchPoliceStatusReport
     */
    public function setSwitchPoliceStatusReport($switchPoliceStatusReport)
    {
        $this->switchPoliceStatusReport = $switchPoliceStatusReport;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputSwitchDetailReport()
    {
        return $this->inputSwitchDetailReport;
    }

    /**
     * @param string|null $inputSwitchDetailReport
     */
    public function setInputSwitchDetailReport($inputSwitchDetailReport)
    {
        $this->inputSwitchDetailReport = $inputSwitchDetailReport;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSwitchVehicleResult()
    {
        return $this->switchVehicleResult;
    }

    /**
     * @param string|null $switchVehicleResult
     */
    public function setSwitchVehicleResult($switchVehicleResult)
    {
        $this->switchVehicleResult = $switchVehicleResult;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputSwitchPoliceStatusReport()
    {
        return $this->inputSwitchPoliceStatusReport;
    }

    /**
     * @param string|null $inputSwitchPoliceStatusReport
     */
    public function setInputSwitchPoliceStatusReport($inputSwitchPoliceStatusReport)
    {
        $this->inputSwitchPoliceStatusReport = $inputSwitchPoliceStatusReport;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPromoteSaleInterest()
    {
        return $this->promoteSaleInterest;
    }

    /**
     * @param string|null $promoteSaleInterest
     */
    public function setPromoteSaleInterest($promoteSaleInterest)
    {
        $this->promoteSaleInterest = $promoteSaleInterest;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInputPromoteSaleInterest()
    {
        return $this->inputPromoteSaleInterest;
    }

    /**
     * @param string|null $inputPromoteSaleInterest
     */
    public function setInputPromoteSaleInterest($inputPromoteSaleInterest)
    {
        $this->inputPromoteSaleInterest = $inputPromoteSaleInterest;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getVehicleDefects()
    {
        return $this->vehicleDefects;
    }

    /**
     * @param string|null $vehicleDefects
     */
    public function setVehicleDefects($vehicleDefects)
    {
        $this->vehicleDefects = $vehicleDefects;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReasonOfDefects()
    {
        return $this->reasonOfDefects;
    }

    /**
     * @param string|null $reasonOfDefects
     */
    public function setReasonOfDefects($reasonOfDefects)
    {
        $this->reasonOfDefects = $reasonOfDefects;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getItemsDiscussed()
    {
        return $this->itemsDiscussed;
    }

    /**
     * @param string|null $itemsDiscussed
     */
    public function setItemsDiscussed($itemsDiscussed)
    {
        $this->itemsDiscussed = $itemsDiscussed;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConcludingRemarksTester()
    {
        return $this->concludingRemarksTester;
    }

    /**
     * @param string|null $concludingRemarksTester
     */
    public function setConcludingRemarksTester($concludingRemarksTester)
    {
        $this->concludingRemarksTester = $concludingRemarksTester;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConcludingRemarksAe()
    {
        return $this->concludingRemarksAe;
    }

    /**
     * @param string|null $concludingRemarksAe
     */
    public function setConcludingRemarksAe($concludingRemarksAe)
    {
        $this->concludingRemarksAe = $concludingRemarksAe;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConcludingRemarksRecommendation()
    {
        return $this->concludingRemarksRecommendation;
    }

    /**
     * @param string|null $concludingRemarksRecommendation
     */
    public function setConcludingRemarksRecommendation($concludingRemarksRecommendation)
    {
        $this->concludingRemarksRecommendation = $concludingRemarksRecommendation;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConcludingRemarksName()
    {
        return $this->concludingRemarksName;
    }

    /**
     * @param string|null $concludingRemarksName
     */
    public function setConcludingRemarksName($concludingRemarksName)
    {
        $this->concludingRemarksName = $concludingRemarksName;

        return $this;
    }
}
