<?php

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

/**
 * A defect that has been observed on a vehicle during a test, as opposed to a
 * potential defect. Once a defect has been added to a vehicle, it becomes an 
 * ObservedDefect.
 * 
 * @see Defect
 */
class ObservedDefect
{
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
     * ObservedDefect constructor.
     *
     * @param string $defectType
     * @param string $lateralLocation
     * @param string $longitudinalLocation
     * @param string $verticalLocation
     * @param string $userComment
     * @param bool   $dangerous
     * @param string $name
     */
    public function __construct(
        $defectType,
        $lateralLocation,
        $longitudinalLocation,
        $verticalLocation,
        $userComment,
        $dangerous,
        $name
    ) {
        $this->defectType = $defectType;
        $this->lateralLocation = $lateralLocation;
        $this->longitudinalLocation = $longitudinalLocation;
        $this->verticalLocation = $verticalLocation;
        $this->userComment = $userComment;
        $this->dangerous = $dangerous;
        $this->name = $name;
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
}
