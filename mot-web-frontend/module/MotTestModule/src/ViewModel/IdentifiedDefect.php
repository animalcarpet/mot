<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

/**
 * A defect that has been observed on a vehicle during a test, as opposed to a
 * potential defect. Once a defect has been added to a vehicle, it becomes an
 * IdentifiedDefect.
 *
 * @see Defect
 */
class IdentifiedDefect
{
    const FAILURE = 'failure';
    const PRS = 'PRS';
    const ADVISORY = 'advisory';

    /**
     * Failure, PRS or advisory.
     *
     * @var string
     */
    private $defectType;

    /**
     * @var string
     */
    private $lateralLocation;

    /**
     * @var string
     */
    private $longitudinalLocation;

    /**
     * @var string
     */
    private $verticalLocation;

    /**
     * A user-added comment to the defect. This is the comment that is typed
     * into the free text box when the user is adding a defect.
     *
     * @var string
     */
    private $userComment;

    /**
     * @var bool
     */
    private $dangerous;

    /**
     * The general description of the defect, e.g. "Body condition or chassis
     * has excessive corrosion, seriously affecting its strength within 30cm
     * of the body mountings".
     *
     * This is created by concatenating the `testItemSelectorDescription` and
     * `failureText` columns from the database.
     *
     * @var string
     */
    private $name;

    /**
     * This is the ID of the Defect. This is what's used as a foreign key in
     * the various database tables to get all the Defect's information.
     *
     * This is NOT the ID of this specific IdentifiedDefect.
     *
     * @var int
     *
     * @see Defect The collection of information to which this property refers
     * @see IdentifiedDefect::$id The unique ID of this exact IdentifiedDefect
     */
    private $defectId;

    /**
     * The ID of this IdentifiedDefect in the database. This is because it's
     * possible to add two defects that are identical, so to differentiate
     * between them we need to use the primary key from the database, i.e.,
     * the row ID.
     *
     * @var int
     */
    private $id;

    /**
     * This specifies if the defect was added in the first MOT test (before an MOT retest).
     *
     * @var bool
     */
    private $onOriginalTest;

    /**
     * @var bool
     */
    private $generated;

    /**
     * @var bool
     */
    private $markedAsRepaired;

    /**
     * IdentifiedDefect constructor.
     *
     * @param string $defectType
     * @param string $lateralLocation
     * @param string $longitudinalLocation
     * @param string $verticalLocation
     * @param string $userComment
     * @param bool   $dangerous
     * @param string $name
     * @param int    $id
     * @param int    $defectId
     * @param bool   $onOriginalTest
     * @param bool   $generated
     * @param bool   $markedAsRepaired
     */
    public function __construct($defectType, $lateralLocation, $longitudinalLocation, $verticalLocation, $userComment, $dangerous, $name, $id, $defectId, $onOriginalTest, $generated, $markedAsRepaired)
    {
        $this->defectType = $defectType;
        $this->lateralLocation = $lateralLocation;
        $this->longitudinalLocation = $longitudinalLocation;
        $this->verticalLocation = $verticalLocation;
        $this->userComment = $userComment;
        $this->dangerous = $dangerous;
        $this->name = $name;
        $this->id = $id;
        $this->defectId = $defectId;
        $this->onOriginalTest = (bool) $onOriginalTest;
        $this->generated = (bool) $generated;
        $this->markedAsRepaired = $markedAsRepaired;
    }

    /**
     * @return bool
     */
    public function isManualAdvisory()
    {
        return !$this->defectId && self::ADVISORY === $this->defectType;
    }

    /**
     * Whether or not the defect has had its location recorded.
     * If $lateralLocation, $longitudinalLocation and $verticalLocation are
     * empty, no location is available.
     *
     * @return bool
     */
    public function hasLocation()
    {
        return !(empty($this->lateralLocation) && empty($this->longitudinalLocation) && empty($this->verticalLocation));
    }

    /**
     * @return string
     */
    public function getDefectType()
    {
        return $this->defectType;
    }

    /**
     * @return string
     */
    public function getLateralLocation()
    {
        return $this->lateralLocation;
    }

    /**
     * @return string
     */
    public function getLongitudinalLocation()
    {
        return $this->longitudinalLocation;
    }

    /**
     * @return string
     */
    public function getVerticalLocation()
    {
        return $this->verticalLocation;
    }

    /**
     * @return string
     */
    public function getUserComment()
    {
        return $this->userComment;
    }

    /**
     * @return bool
     */
    public function isDangerous()
    {
        return $this->dangerous;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDefectId()
    {
        return $this->defectId;
    }

    /**
     * The location displayed on the remove defect screen.
     * e.g, 'Nearside, front, lower'.
     *
     * If no location has been recorded for this IdentifiedDefect, return 'n/a'.
     *
     * @return string
     */
    public function getLocationString()
    {
        if (!$this->hasLocation()) {
            return 'n/a';
        }

        return ucfirst(implode(', ', array_filter([$this->lateralLocation, $this->longitudinalLocation,
                    $this->verticalLocation, ])));
    }

    /**
     * @return bool
     */
    public function isOnOriginalTest()
    {
        return $this->onOriginalTest;
    }

    /**
     * @param bool $onOriginalTest
     */
    public function setOnOriginalTest($onOriginalTest)
    {
        $this->onOriginalTest = $onOriginalTest;
    }

    /**
     * @param bool $isRetest
     *
     * @return bool
     */
    public function isRepairable($isRetest)
    {
        return $isRetest && !$this->generated && $this->onOriginalTest;
    }

    /**
     * @return bool
     */
    public function isRemovable()
    {
        return !$this->generated;
    }

    /**
     * @return bool
     */
    public function isMarkedAsRepaired()
    {
        return $this->markedAsRepaired;
    }
}
