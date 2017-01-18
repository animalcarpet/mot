<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Table(name="mot_test_current_rfr_map", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
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
     * @var ReasonForRejectionType
     *
     * @ORM\OneToOne(targetEntity="ReasonForRejectionType", cascade={"persist"})
     * @ORM\JoinColumn(name="rfr_type_id", referencedColumnName="id", nullable=false)
     */
    private $type;

    /**
     * @var MotTestReasonForRejectionLocation
     *
     * @ORM\OneToOne(targetEntity="MotTestReasonForRejectionLocation", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="mot_test_rfr_location_type_id", referencedColumnName="id", nullable=true)
     */
    private $location;

    /**
     * @var MotTestReasonForRejectionComment
     *
     * @ORM\OneToOne(targetEntity="MotTestReasonForRejectionComment", cascade={"remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="id", referencedColumnName="id", nullable=true)
     */
    private $motTestReasonForRejectionComment;

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
     * @var MotTestReasonForRejectionDescription
     *
     * @ORM\OneToOne(targetEntity="MotTestReasonForRejectionDescription", cascade={"remove"}, fetch="EAGER")
     * @ORM\JoinColumn(name="id", referencedColumnName="id", nullable=true)
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
     * @param ReasonForRejectionType $type
     * @return $this
     */
    public function setType(ReasonForRejectionType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return ReasonForRejectionType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param MotTestReasonForRejectionLocation $location
     * @return MotTestReasonForRejection
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return MotTestReasonForRejectionLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get locationLateral.
     *
     * @return string
     */
    public function getLocationLateral()
    {
        if (!$this->getLocation()) {
            return;
        }

        return $this->getLocation()->getLateral();
    }

    /**
     * Get locationLongitudinal.
     *
     * @return string
     */
    public function getLocationLongitudinal()
    {
        if (!$this->getLocation()) {
            return;
        }

        return $this->getLocation()->getLongitudinal();
    }

    /**
     * Get locationVertical.
     *
     * @return string
     */
    public function getLocationVertical()
    {
        if (!$this->getLocation()) {
            return;
        }

        return $this->getLocation()->getVertical();
    }

    /**
     * @return MotTestReasonForRejectionComment
     */
    public function getMotTestReasonForRejectionComment()
    {
        try {
            return $this->motTestReasonForRejectionComment;
        } catch (EntityNotFoundException $e) {
            return;
        }
    }

    /**
     * @param MotTestReasonForRejectionComment|null $motTestReasonForRejectionComment
     * @return MotTestReasonForRejection
     */
    public function setMotTestReasonForRejectionComment(
        MotTestReasonForRejectionComment $motTestReasonForRejectionComment
    )
    {
        $this->motTestReasonForRejectionComment = $motTestReasonForRejectionComment;
        return $this;
    }

    /**
     * @return bool|MotTestReasonForRejectionComment
     */
    public function popComment()
    {
        $comment = $this->getMotTestReasonForRejectionComment();

        if (is_null($comment)) {
            return false;
        }

        $this->motTestReasonForRejectionComment = null;

        return $comment;
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
        if (is_null($this->getMotTestReasonForRejectionComment())) {
            $this->setMotTestReasonForRejectionComment(new MotTestReasonForRejectionComment());
        }

        $this->getMotTestReasonForRejectionComment()->setComment($comment);

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        if (is_null($this->getMotTestReasonForRejectionComment())) {
            return;
        }

        try {
            return $this->getMotTestReasonForRejectionComment()->getComment();
        } catch (EntityNotFoundException $e) {
            return;
        }

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
     * @param MotTestReasonForRejectionDescription $description
     * @return $this
     */
    public function setCustomDescription(MotTestReasonForRejectionDescription $description)
    {
        $this->customDescription = $description;

        return $this;
    }

    /**
     * @return MotTestReasonForRejectionDescription|void
     */
    public function getCustomDescription()
    {
        if (is_null($this->customDescription)) {
            return;
        }

        try {
            return $this->customDescription;
        } catch (EntityNotFoundException $e) {
            return;
        }
    }

    /**
     * @return bool|MotTestReasonForRejectionDescription
     */
    public function popDescription()
    {
        $description = $this->getCustomDescription();

        if (is_null($description)) {
            return false;
        }

        $this->customDescription = null;

        return $description;
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
        return ($this->getType()->getReasonForRejectionType() === ReasonForRejectionTypeName::FAIL);
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
