<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTest\Model;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;

/**
 * Class OdometerReadingViewObject.
 */
class OdometerReadingViewObject
{
    const IS_NOT_RECORDED = 'Not recorded';
    const IS_NOT_READABLE = 'Unreadable';
    const IS_NOT_PRESENT = 'Missing';

    private $unitMap = [
      OdometerUnit::MILES => 'miles',
      OdometerUnit::KILOMETERS => 'km',
    ];

    private $notices = [];

    /**
     * @var bool
     */
    private $modifiable = true;

    /**
     * @var int
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var string
     */
    private $resultType;

    /**
     * @param int    $value
     * @param string $unit
     * @param string $resultType
     *
     * @return OdometerReadingViewObject
     */
    public static function create($value, $unit, $resultType = OdometerReadingResultType::OK)
    {
        $odometerViewObject = new self();

        $odometerViewObject->setValue($value);
        $odometerViewObject->setUnit($unit);
        $odometerViewObject->setResultType($resultType);

        return $odometerViewObject;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return OdometerReadingViewObject
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     *
     * @return OdometerReadingViewObject
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     * @param string $resultType
     *
     * @return OdometerReadingViewObject
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;

        return $this;
    }

    public function setModifiable($modifiable)
    {
        $this->modifiable = $modifiable;

        return $this;
    }

    public function isModifiable()
    {
        return $this->modifiable;
    }

    /**
     * @param $notices
     *
     * @return $this
     */
    public function setNotices($notices)
    {
        $this->notices = $notices;

        return $this;
    }

    public function getNotice()
    {
        return $this->hasNotice() ? current($this->notices) : null;
    }

    public function hasNotice()
    {
        return !empty($this->notices);
    }

    public function getUnitName()
    {
        if ($this->getUnit() == 'mi') {
            return 'miles';
        } else {
            return $this->getUnit();
        }
    }

    public function isInMiles()
    {
        return $this->getUnit() === self::MILES;
    }

    public function isInKilometers()
    {
        return $this->getUnit() === self::KILOMETERS;
    }

    public function isNotRecorded()
    {
        return self::IS_NOT_RECORDED === $this->getDisplayValue();
    }

    private function isNotReadable()
    {
        return self::IS_NOT_READABLE === $this->getDisplayValue();
    }

    private function isNotPresent()
    {
        return self::IS_NOT_PRESENT === $this->getDisplayValue();
    }

    public function hasNumericValue()
    {
        return !($this->isNotPresent() || $this->isNotReadable() || $this->isNotRecorded());
    }

    public function getDisplayValue()
    {
        if ($this->isOdometerReadingRecoded()) {
            switch ($this->getResultType()) {
                case OdometerReadingResultType::NOT_READABLE:
                    return self::IS_NOT_READABLE;
                case OdometerReadingResultType::NO_ODOMETER:
                    return self::IS_NOT_PRESENT;
                default:
                    return sprintf('%s %s', $this->getValue(), $this->unitMap[$this->getUnit()]);
            }
        } else {
            return self::IS_NOT_RECORDED;
        }
    }

    private function isOdometerReadingRecoded()
    {
        $recordedValueAndUnit = !is_null($this->getValue()) && !is_null($this->getUnit());
        $recordedResultType = !is_null($this->getResultType()) &&
            $this->getResultType() != OdometerReadingResultType::OK;

        return $recordedValueAndUnit or $recordedResultType;
    }
}
