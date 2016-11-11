<?php

namespace DvsaMotTest\Presenter;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\MotTesting\MotTestOptionsDto;
use DvsaCommon\Enum\MotTestTypeCode;

class MotTestOptionsPresenter
{
    /** @var MotTestOptionsDto $motTestOptions */
    private $motTestOptions;
    protected $motTestNumber;

    public function __construct(MotTestOptionsDto $motTestOptions)
    {
        $this->motTestOptions = $motTestOptions;
    }

    /**
     * @return string
     */
    public function displayVehicleMakeAndModel()
    {
        if (empty($this->motTestOptions->getVehicleModel())) {
            return $this->motTestOptions->getVehicleMake();
        }

        return join(' ', [$this->motTestOptions->getVehicleMake(), $this->motTestOptions->getVehicleModel()]);
    }

    /**
     * @return string
     */
    public function displayVehicleRegistrationNumber()
    {
        return $this->motTestOptions->getVehicleRegistrationNumber();
    }

    /**
     * @return string
     */
    public function displayMotTestStartedDate()
    {
        return DateTimeDisplayFormat::textDateTime($this->motTestOptions->getMotTestStartedDate());
    }

    /**
     * @return bool
     */
    public function isMotTestRetest()
    {
        return ($this->motTestOptions->getMotTestTypeDto()->getCode() === MotTestTypeCode::RE_TEST);
    }

    /**
     * @return bool
     */
    public function isNonMotTest()
    {
        return $this->motTestOptions->getMotTestTypeDto()->getCode() === MotTestTypeCode::NON_MOT_TEST;
    }

    /**
     * @return bool
     */
    public function isMotTest()
    {
        return !$this->isMotTestRetest() && !$this->isNonMotTest();
    }

    /**
     * @return bool
     */
    public function getMotTestNumber()
    {
        return $this->motTestNumber;
    }

    /**
     * @param mixed $motTestNumber
     * @return MotTestOptionsPresenter
     */
    public function setMotTestNumber($motTestNumber)
    {
        $this->motTestNumber = $motTestNumber;
        return $this;
    }

    public function getReadableMotTestType()
    {
        switch ($this->motTestOptions->getMotTestTypeDto()->getCode()) {
            case MotTestTypeCode::RE_TEST:
                $type = 'MOT retest';
                break;
            case MotTestTypeCode::NON_MOT_TEST:
                $type = 'Non-MOT test';
                break;
            default:
                $type = 'MOT test';
        }

        return $type;
    }

    public function getReadableMotTestTypeWithIndefiniteArticle()
    {
        switch ($this->motTestOptions->getMotTestTypeDto()->getCode()) {
            case MotTestTypeCode::RE_TEST:
                $type = 'an MOT retest';
                break;
            case MotTestTypeCode::NON_MOT_TEST:
                $type = 'an Non-MOT test';
                break;
            default:
                $type = 'an MOT test';
        }

        return $type;
    }

    public function getPageSubTitle()
    {
        switch ($this->motTestOptions->getMotTestTypeDto()->getCode()) {
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                $type = 'Training test';
                break;
            case MotTestTypeCode::NON_MOT_TEST:
                $type = 'Non-MOT test';
                break;
            default:
                $type = 'MOT testing';
        }

        return $type;
    }
}
