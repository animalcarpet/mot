<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\OdometerReadingDTO;

/**
 * Class OdometerReadingViewObject
 *
 * @package DvsaMotTest\Model
 */
class OdometerReadingViewObject
{
    const IS_NOT_RECORDED = "Not recorded";
    const MILES = 'mi';
    const KILOMETERS = 'km';

    private $readingValuesMap;
    private $notices = [];
    private $modifiable = true;

    public static function create()
    {
        return new OdometerReadingViewObject();
    }

    public function setModifiable($modifiable)
    {
        $this->modifiable = $modifiable;
        return $this;
    }

    public function getModifiable()
    {
        return $this->modifiable;
    }

    /**
     * @param $valuesMap
     *
     * @return $this
     */
    public function setOdometerReadingValuesMap($valuesMap)
    {
        $this->readingValuesMap = $valuesMap ? : [];
        return $this;
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

    public function getValue()
    {
        if (is_object($this->readingValuesMap)) {
            /** @var OdometerReadingDTO $readingValue */
            $readingValue = $this->readingValuesMap;

            return $readingValue->getValue();
        } else {
            return isset($this->readingValuesMap['value']) ? $this->readingValuesMap['value'] : null;
        }
    }

    public function getUnit()
    {
        if (is_object($this->readingValuesMap)) {
            /** @var OdometerReadingDTO $readingValue */
            $readingValue = $this->readingValuesMap;

            return $readingValue->getUnit();
        } else {
            return isset($this->readingValuesMap['unit']) ? $this->readingValuesMap['unit'] : null;
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

    public function getResultType()
    {
        if (is_object($this->readingValuesMap)) {
            /** @var OdometerReadingDTO $readingValue */
            $readingValue = $this->readingValuesMap;

            return $readingValue->getResultType();
        } else {
            return isset($this->readingValuesMap['resultType']) ? $this->readingValuesMap['resultType'] : null;
        }
    }

    public function isNotRecorded()
    {
        return self::IS_NOT_RECORDED === $this->getDisplayValue();
    }

    public function getDisplayValue()
    {
        if ($this->readingValuesMap != null) {
            if (is_object($this->readingValuesMap)) {
                /** @var OdometerReadingDTO $readingValue */
                $readingValue = $this->readingValuesMap;
                $value = $readingValue->getValue();
                $unit = $readingValue->getUnit();
                $type = $readingValue->getResultType();
            } else {
                $value = $this->readingValuesMap['value'];
                $unit = $this->readingValuesMap['unit'];
                $type = $this->readingValuesMap['resultType'];
            }

            if ($unit == 'mi') {
                $unit = 'miles';
            }

            switch ($type) {
                case OdometerReadingResultType::NOT_READABLE:
                    return "Not readable";
                case OdometerReadingResultType::NO_ODOMETER:
                    return "Vehicle does not have an odometer";
                default:
                    return "$value $unit";
            }
        } else {
            return "Not recorded";
        }
    }
}
