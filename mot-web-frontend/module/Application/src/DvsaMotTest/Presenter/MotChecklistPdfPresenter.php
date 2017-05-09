<?php

namespace DvsaMotTest\Presenter;

use DateTime;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaMotTest\Model\MotChecklistPdfField;

class MotChecklistPdfPresenter implements AutoWireableInterface
{
    const NORMAL_BOX_LENGTH = 21;
    const TESTER_NAME_LENGTH = 34;
    const FONT_SIZE_8 = 8;
    const FONT_SIZE_9 = 9;

    protected $fontColor = '#000000';
    protected $firstLineY = 750;
    protected $secondLineY = 710;
    protected $thirdLineY = 676.5;
    protected $firstColumnX = 50;
    protected $secondColumnX = 177;
    protected $thirdColumnX = 305;
    protected $fourthColumnX = 433;

    /**
     * @var MotTest
     */
    protected $motTest;
    /**
     * @var MotFrontendIdentityInterface
     */
    protected $identity;

    /**
     * @var DvsaVehicle
     */
    protected $vehicle;

    /**
     * @return string
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    public function setMotTest(MotTest $motTest)
    {
        $this->motTest = $motTest;

        return $this;
    }

    public function setVehicle(DvsaVehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function setIdentity(MotFrontendIdentityInterface $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return MotChecklistPdfField[]
     */
    public function getDataFields()
    {
        $startDate = new DateTime($this->motTest->getStartedDate());
        $firstUseDate = $this->vehicle->getFirstUsedDate();
        if (!is_null($firstUseDate)) {
            $firstUseDate = new DateTime($this->vehicle->getFirstUsedDate());
        }
        $site = $this->motTest->getSiteId();

        $fields = [
            new MotChecklistPdfField(mb_substr($this->identity->getDisplayName(), 0, static::TESTER_NAME_LENGTH), 365, 768, static::FONT_SIZE_8),
            new MotChecklistPdfField(DateTimeDisplayFormat::dateShort($startDate), 330, $this->firstLineY, static::FONT_SIZE_8),
            new MotChecklistPdfField(DateTimeDisplayFormat::time($startDate), 412, $this->firstLineY, static::FONT_SIZE_8),
            new MotChecklistPdfField(!is_null($site) ? $site['siteNumber'] : '', 496, $this->firstLineY, static::FONT_SIZE_8),
            new MotChecklistPdfField($this->vehicle->getRegistration(), $this->firstColumnX, $this->secondLineY, static::FONT_SIZE_9),
            new MotChecklistPdfField($this->vehicle->getVin(), $this->secondColumnX, $this->secondLineY, static::FONT_SIZE_9),
            new MotChecklistPdfField(mb_substr($this->vehicle->getMakeName(), 0, static::NORMAL_BOX_LENGTH), $this->thirdColumnX, $this->secondLineY, static::FONT_SIZE_9),
            new MotChecklistPdfField(mb_substr($this->vehicle->getModelName(), 0, static::NORMAL_BOX_LENGTH), $this->fourthColumnX, $this->secondLineY, static::FONT_SIZE_9),
            new MotChecklistPdfField(!is_null($firstUseDate) ? DateTimeDisplayFormat::dateShort($firstUseDate) : '', $this->thirdColumnX, $this->thirdLineY, static::FONT_SIZE_9),
            new MotChecklistPdfField($this->vehicle->getCylinderCapacity(), $this->firstColumnX, $this->thirdLineY, static::FONT_SIZE_9),
        ];

        if (!$this->isClass1or2Vehicle()) {
            $fields[] = new MotChecklistPdfField($this->pickVehicleWeight(), $this->fourthColumnX, $this->thirdLineY, static::FONT_SIZE_9);
        }

        return $fields;
    }

    public function isClass1or2Vehicle()
    {
        return in_array($this->vehicle->getVehicleClass()->getCode(), VehicleClassGroup::getGroupAClasses());
    }

    public function pickVehicleWeight()
    {
        $vehicleWeight = '';

        if (in_array($this->vehicle->getVehicleClass()->getCode(), VehicleClassCode::getGroupBClasses())) {
            $brakeTestResult = $this->motTest->getBrakeTestResult();

            if (!is_null($brakeTestResult)) {
                $brakeTestResultClass3ndAbove = new BrakeTestResultClass3AndAbove($brakeTestResult);
                $vehicleWeight = $brakeTestResultClass3ndAbove->getVehicleWeight();
            } else {
                $vehicleWeight = $this->motTest->getPreviousTestVehicleWight();
            }
        }

        return $vehicleWeight;
    }
}
