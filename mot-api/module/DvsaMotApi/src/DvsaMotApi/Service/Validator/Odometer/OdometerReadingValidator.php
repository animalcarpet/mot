<?php

namespace DvsaMotApi\Service\Validator\Odometer;

use Api\Check\CheckMessage as CM;
use Api\Check\CheckResult;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use ZendPdf\BinaryParser\DataSource\String;

/**
 * Encapsulates all validation rules for OdometerReading
 *
 * Class OdometerReadingValidator
 *
 * @package DvsaMotApi\Service\Odometer\Validator
 */
class OdometerReadingValidator
{
    /**
     * @var integer
     */
    private $value;

    /**
     * @var String
     */
    private $unit;

    /**
     * @var string
     */
    private $resultType;

    /**
     * @param $value
     * @param $unit
     * @param $resultType
     * @param null $fieldContext
     * @return CheckResult
     */
    public function validate($value, $unit, $resultType, $fieldContext = null)
    {
        $this->setValue($value)->setUnit($unit)->setResultType($resultType);

        $result = CheckResult::ok();
        $errorCodeInvalidData = BadRequestException::ERROR_CODE_INVALID_DATA;

        if (!OdometerReadingResultType::isValid($this->getResultType())) {
            $result->add(
                CM::create($fieldContext)->field("resultType")->code(BadRequestException::ERROR_CODE_INVALID_DATA)
                    ->text("Invalid odometer result type")
            );
        }
        if ($this->getResultType() === OdometerReadingResultType::OK) {
            $value = $this->getValue();
            if (!is_null($value) && (!is_int($value) || $value < 0) || is_null($value)) {
                $result->add(
                    CM::create($fieldContext)->field("value")->code($errorCodeInvalidData)->text(
                        "Invalid odometer value"
                    )
                );
            }
            if (!OdometerUnit::isValid($this->getUnit())) {
                $result->add(
                    CM::create($fieldContext)->field("unit")->code(BadRequestException::ERROR_CODE_INVALID_DATA)
                        ->text('Invalid odometer unit')
                );
            }
        } else {
            if (!is_null($this->getValue())) {
                $result->add(
                    CM::create($fieldContext)->field("value")->code(
                        BadRequestException::ERROR_CODE_INVALID_ENTITY_STATE
                    )
                        ->text("Odometer value given though result type is not OK")
                );
            }
            if (!is_null($this->getUnit())) {
                $result->add(
                    CM::create($fieldContext)->field("unit")->code(BadRequestException::ERROR_CODE_INVALID_ENTITY_STATE)
                        ->text("Odometer unit given though result type is not OK")
                );
            }
        }
        return $result;
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
     * @return OdometerReadingValidator
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return String
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param String $unit
     * @return OdometerReadingValidator
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
     * @return OdometerReadingValidator
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;
        return $this;
    }
}
