<?php

namespace DvsaMotApi\Dto;

use DvsaCommon\Dto\Common\OdometerReadingDTO;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class ReplacementCertificateDraftDTO
 *
 * @package DvsaMotApi\Dto
 */
class ReplacementCertificateDraftChangeDTO
{

    private $changedMap = [];

    /**
     * @var string $primaryColour colour code
     */
    private $primaryColour;
    /**
     * @var string $secondaryColour colour code
     */
    private $secondaryColour;
    /**
     * @var string vrm - vehicle registration mark
     */
    private $vrm;
    /**
     * @var string $vin
     */
    private $vin;
    /**
     * @var OdometerReadingDTO $odometerReading
     */
    private $odometerReading;
    /**
     * @var string $model
     */
    private $model;
    /**
     * @var string $make
     */
    private $make;
    /**
     * @var int $countryOfRegistration
     */
    private $countryOfRegistration;
    /**
     * @var string $reasonForReplacement
     */
    private $reasonForReplacement;

    /**
     * @var string $vtsSiteNumber
     */
    private $vtsSiteNumber;
    /**
     * @var string $expiryDate
     */
    private $expiryDate;

    /**
     * @var string $customMake
     */
    private $customMake;

    /**
     * @var string $customModel
     */
    private $customModel;

    /**
     * @var string $reasonForDifferentTester
     */
    private $reasonForDifferentTester;

    /**
     * @var boolean $isVinVrmExpiryChanged
     */
    private $isVinVrmExpiryChanged;

    /**
     * @var boolean $includeInMismatchFile
     */
    private $includeInMismatchFile;

    /**
     * @var boolean $includeInPassFile
     */
    private $includeInPassFile;

    /**
     * @var boolean $isDvlaImportProcess
     */
    private $isDvlaImportProcess = false;

    public static function create()
    {
        return new static();
    }

    /**
     * @param $data
     *
     * @return ReplacementCertificateDraftChangeDTO
     */
    public static function fromDataArray($data)
    {
        $d = self::create();
        $hasKey = function ($key) use (&$data) {
            return array_key_exists($key, $data);
        };
        if ($hasKey('primaryColour')) {
            $d->setPrimaryColour($data['primaryColour']);
        }
        if ($hasKey('secondaryColour')) {
            $d->setSecondaryColour($data['secondaryColour']);
        }
        if ($hasKey('vin')) {
            $d->setVin(strtoupper($data['vin']));
        }
        if ($hasKey('vrm')) {
            $d->setVrm(strtoupper($data['vrm']));
        }
        if ($hasKey('countryOfRegistration')) {
            $d->setCountryOfRegistration($data['countryOfRegistration']);
        }
        if ($hasKey('make')) {
            $d->setMake($data['make']);
        }
        if ($hasKey('model')) {
            $d->setModel($data['model']);
        }
        if ($hasKey('vtsSiteNumber')) {
            $d->setVtsSiteNumber($data['vtsSiteNumber']);
        }
        if ($hasKey('expiryDate')) {
            $d->setExpiryDate($data['expiryDate']);
        }
        if ($hasKey('reasonForReplacement')) {
            $d->setReasonForReplacement($data['reasonForReplacement']);
        }
        if ($hasKey('reasonForDifferentTester')) {
            $d->setReasonForDifferentTester($data['reasonForDifferentTester']);
        }
        if ($hasKey('odometerReading')) {
            $d->setOdometerReading(
                OdometerReadingDTO::create()
                    ->setValue(ArrayUtils::tryGet($data['odometerReading'], 'value'))
                    ->setUnit(ArrayUtils::tryGet($data['odometerReading'], 'unit'))
                    ->setResultType(ArrayUtils::tryGet($data['odometerReading'], 'resultType'))
            );
        }

        if ($hasKey('customMake')) {
            $d->setCustomMake($data['customMake']);
        }
        if ($hasKey('customModel')) {
            $d->setCustomModel($data['customModel']);
        }
        if ($hasKey('isVinVrmExpiryChanged')) {
            $d->setIsVinVrmExpiryChanged($data['isVinVrmExpiryChanged']);
        }
        if ($hasKey('includeInMismatchFile')) {
            $d->setIncludeInMismatchFile($data['includeInMismatchFile']);
        }
        if ($hasKey('includeInPassFile')) {
            $d->setIncludeInPassFile($data['includeInPassFile']);
        }
        return $d;
    }

    /**
     * @param string $reasonForDifferentTester
     *
     * @return $this
     */
    public function setReasonForDifferentTester($reasonForDifferentTester)
    {
        $this->setAsChanged('reasonForDifferentTester');
        $this->reasonForDifferentTester = $reasonForDifferentTester;
        return $this;
    }

    /**
     * @return string
     */
    public function getReasonForDifferentTester()
    {
        return $this->reasonForDifferentTester;
    }

    /**
     * @return bool
     */
    public function isReasonForDifferentTesterSet()
    {
        return isset($this->changedMap['reasonForDifferentTester']);
    }

    /**
     * Primary key of country of registration
     * @param int $countryOfRegistration
     *
     * @return $this
     */
    public function setCountryOfRegistration($countryOfRegistration)
    {
        $this->countryOfRegistration = $countryOfRegistration;
        $this->setAsChanged('countryOfRegistration');
        return $this;
    }

    /**
     * Primary key of country of registration
     * @return int
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @return bool
     */
    public function isCountryOfRegistrationSet()
    {
        return isset($this->changedMap['countryOfRegistration']);
    }

    /**
     * @param string $make make code
     *
     * @return $this
     */
    public function setMake($make)
    {
        $this->make = $make;
        $this->setAsChanged('make');
        return $this;
    }

    /**
     * @return string make code
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @return bool
     */
    public function isMakeSet()
    {
        return isset($this->changedMap['make']);
    }

    /**
     * @param string $model model code
     *
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->setAsChanged('model');
        return $this;
    }

    /**
     * @return string model code
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return bool
     */
    public function isModelSet()
    {
        return isset($this->changedMap['model']);
    }

    /**
     * @param \DvsaCommon\Dto\Common\OdometerReadingDTO $odometerReading
     *
     * @return $this
     */
    public function setOdometerReading($odometerReading)
    {
        $this->odometerReading = $odometerReading;
        $this->setAsChanged('odometerReading');
        return $this;
    }

    /**
     * @return \DvsaCommon\Dto\Common\OdometerReadingDTO
     */
    public function getOdometerReading()
    {
        return $this->odometerReading;
    }

    /**
     * @return bool
     */
    public function isOdometerReadingSet()
    {
        return isset($this->changedMap['odometerReading']);
    }

    /**
     * @param string $primaryColour primary colour code
     *
     * @return $this
     */
    public function setPrimaryColour($primaryColour)
    {
        $this->primaryColour = $primaryColour;
        $this->setAsChanged('primaryColour');
        return $this;
    }

    /**
     * @return string primary colour code
     */
    public function getPrimaryColour()
    {
        return $this->primaryColour;
    }

    /**
     * @return bool
     */
    public function isPrimaryColourSet()
    {
        return isset($this->changedMap['primaryColour']);
    }

    /**
     * @param string $reasonForReplacement
     *
     * @return $this
     */
    public function setReasonForReplacement($reasonForReplacement)
    {
        $this->reasonForReplacement = $reasonForReplacement;
        $this->setAsChanged('reasonForReplacement');
        return $this;
    }

    /**
     * @return string
     */
    public function getReasonForReplacement()
    {
        return $this->reasonForReplacement;
    }

    /**
     * @return bool
     */
    public function isReasonForReplacementSet()
    {
        return isset($this->changedMap['reasonForReplacement']);
    }

    /**
     * @param string $secondaryColour colour code
     *
     * @return $this
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;
        $this->setAsChanged('secondaryColour');
        return $this;
    }

    /**
     * @return string colour code
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @return bool
     */
    public function isSecondaryColourSet()
    {
        return isset($this->changedMap['secondaryColour']);
    }

    /**
     * @param string $vin
     *
     * @return $this
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
        $this->setAsChanged('vin');
        return $this;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @return bool
     */
    public function isVinSet()
    {
        return isset($this->changedMap['vin']);
    }

    /**
     * @param string $vrm
     *
     * @return $this
     */
    public function setVrm($vrm)
    {
        $this->vrm = $vrm;
        $this->setAsChanged('vrm');
        return $this;
    }

    /**
     * @return bool
     */
    public function isVrmSet()
    {
        return isset($this->changedMap['vrm']);
    }

    /**
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * @param string $vtsSiteNumber
     *
     * @return $this
     */
    public function setVtsSiteNumber($vtsSiteNumber)
    {
        $this->vtsSiteNumber = $vtsSiteNumber;
        $this->setAsChanged('vtsSiteNumber');
        return $this;
    }

    /**
     * @return string
     */
    public function getVtsSiteNumber()
    {
        return $this->vtsSiteNumber;
    }

    /**
     * @return bool
     */
    public function isVtsSiteNumberSet()
    {
        return isset($this->changedMap['vtsSiteNumber']);
    }

    /**
     * @param string $expiryDate
     *
     * @return $this
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;
        $this->setAsChanged('expiryDate');
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return bool
     */
    public function isExpiryDateSet()
    {
        return isset($this->changedMap['expiryDate']);
    }

    /**
     * @return string
     */
    public function getCustomMake()
    {
        return $this->customMake;
    }

    /**
     * @param string $customMake
     */
    public function setCustomMake($customMake)
    {
        $this->setAsChanged('customMake');
        $this->customMake = $customMake;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCustomMakeSet()
    {
        return isset($this->changedMap['customMake']);
    }

    /**
     * @return string
     */
    public function getCustomModel()
    {
        return $this->customModel;
    }

    /**
     * @return bool
     */
    public function isCustomModelSet()
    {
        return isset($this->changedMap['customModel']);
    }

    /**
     * @param string $customModel
     */
    public function setCustomModel($customModel)
    {
        $this->setAsChanged('customModel');
        $this->customModel = $customModel;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDataChanged()
    {
        return !empty($this->changedMap);
    }

    /**
     * @param $property
     */
    private function setAsChanged($property)
    {
        $this->changedMap[$property] = true;
    }

    /**
     * @param boolean $vinVrmExpiryChanged
     * @return $this
     */
    public function setIsVinVrmExpiryChanged($vinVrmExpiryChanged)
    {
        $this->isVinVrmExpiryChanged = $vinVrmExpiryChanged;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isVinVrmExpiryChanged()
    {
        return $this->isVinVrmExpiryChanged;
    }

    /**
     * @param boolean $includeInMismatch
     * @return $this
     */
    public function setIncludeInMismatchFile($includeInMismatch)
    {
        $this->includeInMismatchFile = $includeInMismatch;
        return $this;
    }

    /**
     * @return boolean
     */
    public function includeInMismatchFile()
    {
        return $this->includeInMismatchFile;
    }

    /**
     * @param boolean $includeInPassFile
     * @return $this
     */
    public function setIncludeInPassFile($includeInPassFile)
    {
        $this->includeInPassFile = $includeInPassFile;
        return $this;
    }

    /**
     * @return boolean
     */
    public function includeInPassFile()
    {
        return $this->includeInPassFile;
    }

    /**
     * @param boolean $isDvlaImportProcess
     * @return $this
     */
    public function setIsDvlaImportProcess($isDvlaImportProcess)
    {
        $this->isDvlaImportProcess = $isDvlaImportProcess;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDvlaImportProcess()
    {
        return $this->isDvlaImportProcess;
    }

}
