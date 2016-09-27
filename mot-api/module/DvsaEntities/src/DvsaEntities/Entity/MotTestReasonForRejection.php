<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTest.
 *
 * @ORM\Table(name="mot_test_rfr_map", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity
 */
class MotTestReasonForRejection extends Entity
{
    use CommonIdentityTrait;

    const LOCATION_LONGITUDINAL_FRONT = 'front';
    const LOCATION_LONGITUDINAL_REAR = 'rear';

    /**
     * @var \DvsaEntities\Entity\MotTest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTest", inversedBy="motTestReasonForRejections")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     * })
     */
    private $motTest;

    /**
     * @var integer
     *
     * @ORM\Column(name="mot_test_id", type="integer", nullable=false)
     */
    private $motTestId;

    /**
     * @var ReasonForRejection
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\ReasonForRejection")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="rfr_id", referencedColumnName="id")
     * })
     */
    private $reasonForRejection;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="location_lateral", type="string", length=50, nullable=true)
     */
    private $locationLateral;

    /**
     * @var string
     *
     * @ORM\Column(name="location_longitudinal", type="string", length=50, nullable=true)
     */
    private $locationLongitudinal;

    /**
     * @var string
     *
     * @ORM\Column(name="location_vertical", type="string", length=50, nullable=true)
     */
    private $locationVertical;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="failure_dangerous", type="boolean", nullable=false)
     */
    private $failureDangerous;

    /**
     * @var boolean
     *
     * @ORM\Column(name="generated", type="boolean", nullable=false)
     */
    private $generated;

    /**
     * @var string
     *
     * @ORM\Column(name="custom_description", type="string", length=100, nullable=true)
     */
    private $customDescription;

    #TO DO - refactor.
    /**
     * @var boolean
     *
     * @ORM\Column(name="on_original_test", type="boolean", nullable=false)
     */
    private $onOriginalTest;

    /**
     * @var MotTestReasonForRejectionMarkedAsRepaired
     *
     * @ORM\OneToOne(targetEntity="MotTestReasonForRejectionMarkedAsRepaired", mappedBy="motTestRfr",
     *      orphanRemoval=true, cascade={"remove"}, fetch="EXTRA_LAZY")
     */
    private $markedAsRepaired;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setFailureDangerous(false);
        $this->setGenerated(false);
        $this->setOnOriginalTest(false);
    }

    /**
     * @return MotTestReasonForRejectionMarkedAsRepaired
     */
    public function getMarkedAsRepaired()
    {
        return $this->markedAsRepaired;
    }

    /**
     * @return bool
     */
    public function isMarkedAsRepaired()
    {
        return (null !== $this->getMarkedAsRepaired());
    }

    /**
     * Removes the association with MotTestReasonForRejectionMarkedAsRepaired.
     */
    public function undoMarkedAsRepaired()
    {
        $this->markedAsRepaired = null;
    }

    /**
     * Set MotTest.
     *
     * @param MotTest $motTest
     *
     * @return MotTestReasonForRejection
     */
    public function setMotTest(MotTest $motTest = null)
    {
        $this->motTest = $motTest;

        return $this;
    }

    /**
     * Get MotTest.
     *
     * @return MotTest
     */
    public function getMotTest()
    {
        return $this->motTest;
    }

    /**
     * @param int $motTestId
     *
     * @return MotTestReasonForRejection
     */
    public function setMotTestId($motTestId)
    {
        $this->motTestId = $motTestId;

        return $this;
    }

    /**
     * @return int
     */
    public function getMotTestId()
    {
        return $this->motTestId;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return MotTestReasonForRejection
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set locationLateral.
     *
     * @param string $locationLateral
     *
     * @return MotTestReasonForRejection
     */
    public function setLocationLateral($locationLateral)
    {
        $this->locationLateral = $locationLateral;

        return $this;
    }

    /**
     * Get locationLateral.
     *
     * @return string
     */
    public function getLocationLateral()
    {
        return $this->locationLateral;
    }

    /**
     * Set locationLongitudinal.
     *
     * @param string $locationLongitudinal
     *
     * @return MotTestReasonForRejection
     */
    public function setLocationLongitudinal($locationLongitudinal)
    {
        $this->locationLongitudinal = $locationLongitudinal;

        return $this;
    }

    /**
     * Get locationLongitudinal.
     *
     * @return string
     */
    public function getLocationLongitudinal()
    {
        return $this->locationLongitudinal;
    }

    /**
     * Set locationVertical.
     *
     * @param string $locationVertical
     *
     * @return MotTestReasonForRejection
     */
    public function setLocationVertical($locationVertical)
    {
        $this->locationVertical = $locationVertical;

        return $this;
    }

    /**
     * Get locationVertical.
     *
     * @return string
     */
    public function getLocationVertical()
    {
        return $this->locationVertical;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return MotTestReasonForRejection
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set failureDangerous.
     *
     * @param boolean $failureDangerous
     *
     * @return MotTestReasonForRejection
     */
    public function setFailureDangerous($failureDangerous)
    {
        $this->failureDangerous = $failureDangerous;

        return $this;
    }

    /**
     * Get failureDangerous.
     *
     * @return boolean
     */
    public function getFailureDangerous()
    {
        return $this->failureDangerous;
    }

    /**
     * @param boolean $generated
     *
     * @return MotTestReasonForRejection
     */
    public function setGenerated($generated)
    {
        $this->generated = $generated;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getGenerated()
    {
        return $this->generated;
    }

    /**
     * Generated RFRs can't be deleted by the end-user, other than
     * as part of updating a brake test result.
     *
     * @return boolean
     */
    public function getCanBeDeleted()
    {
        return !$this->generated;
    }

    /**
     * @param string $value
     *
     * @return MotTestReasonForRejection
     */
    public function setCustomDescription($value)
    {
        $this->customDescription = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomDescription()
    {
        return $this->customDescription;
    }

    public function __clone()
    {
        $this->id = 0;
    }

    /**
     * @param boolean $onOriginalTest
     *
     * @return MotTestReasonForRejection
     */
    public function setOnOriginalTest($onOriginalTest)
    {
        $this->onOriginalTest = $onOriginalTest;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getOnOriginalTest()
    {
        return $this->onOriginalTest;
    }

    public function isFail()
    {
        return ($this->getType() === ReasonForRejectionTypeName::FAIL);
    }

    /**
     * @return ReasonForRejection
     */
    public function getReasonForRejection()
    {
        return $this->reasonForRejection;
    }

    /**
     * @param ReasonForRejection $reasonForRejection
     *
     * @return $this
     */
    public function setReasonForRejection($reasonForRejection)
    {
        $this->reasonForRejection = $reasonForRejection;

        return $this;
    }

    /**
     * @throws NotFoundException
     *
     * @return string
     */
    public function getEnglishName()
    {
        $rfr = $this->getReasonForRejection();
        if (is_null($rfr)) {
            return 'Manual Advisory'; // TODO VM-3386 - resolve manual advisories problem
        }
        foreach ($rfr->getDescriptions() as $description) {
            if ($description->getLanguage()->getCode() === LanguageTypeCode::ENGLISH) {
                return $description->getName();
            }
        }
        throw new NotFoundException(ReasonForRejectionDescription::class);
    }
}
