<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ReplacementCertificateDraft
 *
 * @ORM\Table(name="certificate_replacement_draft",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\ReplacementCertificateDraftRepository")
 */
class CertificateReplacementDraft extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var CountryOfRegistration
     *
     * @ORM\ManyToOne(targetEntity="CountryOfRegistration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="country_of_registration_id", referencedColumnName="id")
     * })
     */
    private $countryOfRegistration;

    /**
     * @var CertificateChangeDifferentTesterReason
     *
     * @ORM\ManyToOne(targetEntity="CertificateChangeDifferentTesterReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="different_tester_reason_id", referencedColumnName="id")
     * })
     */
    private $differentTesterReason;

    /**
     * @var EmptyVinReason
     *
     * @ORM\ManyToOne(targetEntity="EmptyVinReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empty_vin_reason_id", referencedColumnName="id")
     * })
     */
    private $emptyVinReason;

    /**
     * @var EmptyVrmReason
     *
     * @ORM\ManyToOne(targetEntity="EmptyVrmReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="empty_vrm_reason_id", referencedColumnName="id")
     * })
     */
    private $emptyVrmReason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="include_in_mismatch_file", type="boolean", length=1, nullable=true)
     */
    private $includeInMismatchFile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="include_in_passes_file", type="boolean", length=1, nullable=true)
     */
    private $includeInPassFile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_deleted", type="boolean", length=1, nullable=false)
     */
    private $deleted = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_vin_vrm_expiry_changed", type="boolean", length=1, nullable=true)
     */
    private $vinVrmExpiryChanged;

    /**
     * @var Make
     *
     * @ORM\ManyToOne(targetEntity="Make")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="make_id", referencedColumnName="id")
     * })
     */
    private $make;

    /**
     * @var string
     *
     * @ORM\Column(name="make_name", type="string", nullable=true)
     */
    private $makeName;

    /**
     * @var ModelDetail
     *
     * @ORM\ManyToOne(targetEntity="ModelDetail", fetch="EAGER")
     * @ORM\JoinColumn(name="model_detail_id", referencedColumnName="id", nullable=true)
     */
    private $modelDetail;

    /**
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id"),
     * })
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="model_name", type="string", nullable=true)
     */
    private $modelName;

    /**
     * @var MotTest
     *
     * @ORM\ManyToOne(targetEntity="MotTest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     * })
     */
    private $motTest;

    /**
     * @var integer
     *
     * @ORM\Column(name="mot_test_version", type="integer", length=11, nullable=false)
     */
    private $motTestVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="odometer_result_type", type="string", length=10, nullable=true)
     */
    private $odometerResultType;

    /**
     * @var string
     *
     * @ORM\Column(name="odometer_unit", type="string", length=2, nullable=true)
     */
    private $odometerUnit;

    /**
     * @var integer
     *
     * @ORM\Column(name="odometer_value", type="integer", length=11, nullable=true)
     */
    private $odometerValue;

    /**
     * @var Colour
     *
     * @ORM\ManyToOne(targetEntity="Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="primary_colour_id", referencedColumnName="id")
     * })
     */
    private $primaryColour;

    /**
     * @var string
     *
     * @ORM\Column(name="replacement_reason", type="text", nullable=true)
     */
    private $reasonForReplacement;

    /**
     * @var Colour
     *
     * @ORM\ManyToOne(targetEntity="Colour")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secondary_colour_id", referencedColumnName="id")
     * })
     */
    private $secondaryColour;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_testing_station_id", referencedColumnName="id")
     * })
     */
    private $vehicleTestingStation;

    /**
     * @var string
     *
     * @ORM\Column(name="vrm", type="string", length=20, nullable=true)
     */
    private $vrm;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="string", length=30, nullable=true)
     */
    private $vin;

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return CountryOfRegistration
     */
    public function getCountryOfRegistration()
    {
        return $this->countryOfRegistration;
    }

    /**
     * @param CountryOfRegistration $countryOfRegistration
     * @return CertificateReplacementDraft
     */
    public function setCountryOfRegistration($countryOfRegistration)
    {
        $this->countryOfRegistration = $countryOfRegistration;
        return $this;
    }

    /**
     * @return CertificateChangeDifferentTesterReason
     */
    public function getDifferentTesterReason()
    {
        return $this->differentTesterReason;
    }

    /**
     * @param CertificateChangeDifferentTesterReason $differentTesterReason
     * @return CertificateReplacementDraft
     */
    public function setDifferentTesterReason($differentTesterReason)
    {
        $this->differentTesterReason = $differentTesterReason;
        return $this;
    }

    /**
     * @return EmptyVinReason
     */
    public function getEmptyVinReason()
    {
        return $this->emptyVinReason;
    }

    /**
     * @param EmptyVinReason $emptyVinReason
     * @return CertificateReplacementDraft
     */
    public function setEmptyVinReason($emptyVinReason)
    {
        $this->emptyVinReason = $emptyVinReason;
        return $this;
    }

    /**
     * @return EmptyVrmReason
     */
    public function getEmptyVrmReason()
    {
        return $this->emptyVrmReason;
    }

    /**
     * @param EmptyVrmReason $emptyVrmReason
     * @return CertificateReplacementDraft
     */
    public function setEmptyVrmReason($emptyVrmReason)
    {
        $this->emptyVrmReason = $emptyVrmReason;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @param \DateTime $expiryDate
     * @return CertificateReplacementDraft
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIncludeInMismatchFile()
    {
        return $this->includeInMismatchFile;
    }

    /**
     * @param boolean $includeInMismatchFile
     * @return CertificateReplacementDraft
     */
    public function setIncludeInMismatchFile($includeInMismatchFile)
    {
        $this->includeInMismatchFile = $includeInMismatchFile;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIncludeInPassFile()
    {
        return $this->includeInPassFile;
    }

    /**
     * @param boolean $includeInPassFile
     * @return CertificateReplacementDraft
     */
    public function setIncludeInPassFile($includeInPassFile)
    {
        $this->includeInPassFile = $includeInPassFile;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return CertificateReplacementDraft
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isVinVrmExpiryChanged()
    {
        return $this->vinVrmExpiryChanged;
    }

    /**
     * @param boolean $vinVrmExpiryChanged
     * @return CertificateReplacementDraft
     */
    public function setVinVrmExpiryChanged($vinVrmExpiryChanged)
    {
        $this->vinVrmExpiryChanged = $vinVrmExpiryChanged;
        return $this;
    }

    /**
     * @return Make
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param Make $make
     * @return CertificateReplacementDraft
     */
    public function setMake($make)
    {
        $this->make = $make;
        return $this;
    }

    /**
     * @return string
     */
    public function getMakeName()
    {
        return $this->makeName;
    }

    /**
     * @param string $makeName
     * @return CertificateReplacementDraft
     */
    public function setMakeName($makeName)
    {
        $this->makeName = $makeName;
        return $this;
    }

    /**
     * @return ModelDetail
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * @param ModelDetail $modelDetail
     * @return CertificateReplacementDraft
     */
    public function setModelDetail($modelDetail)
    {
        $this->modelDetail = $modelDetail;
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     * @return CertificateReplacementDraft
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param string $modelName
     * @return CertificateReplacementDraft
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    /**
     * @return MotTest
     */
    public function getMotTest()
    {
        return $this->motTest;
    }

    /**
     * @param MotTest $motTest
     * @return CertificateReplacementDraft
     */
    public function setMotTest($motTest)
    {
        $this->motTest = $motTest;
        return $this;
    }

    /**
     * @return int
     */
    public function getMotTestVersion()
    {
        return $this->motTestVersion;
    }

    /**
     * @param int $motTestVersion
     * @return CertificateReplacementDraft
     */
    public function setMotTestVersion($motTestVersion)
    {
        $this->motTestVersion = $motTestVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getOdometerResultType()
    {
        return $this->odometerResultType;
    }

    /**
     * @param string $odometerResultType
     * @return CertificateReplacementDraft
     */
    public function setOdometerResultType($odometerResultType)
    {
        $this->odometerResultType = $odometerResultType;
        return $this;
    }

    /**
     * @return string
     */
    public function getOdometerUnit()
    {
        return $this->odometerUnit;
    }

    /**
     * @param string $odometerUnit
     * @return CertificateReplacementDraft
     */
    public function setOdometerUnit($odometerUnit)
    {
        $this->odometerUnit = $odometerUnit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOdometerValue()
    {
        return $this->odometerValue;
    }

    /**
     * @param int $odometerValue
     * @return CertificateReplacementDraft
     */
    public function setOdometerValue($odometerValue)
    {
        $this->odometerValue = $odometerValue;
        return $this;
    }

    /**
     * @return Colour
     */
    public function getPrimaryColour()
    {
        return $this->primaryColour;
    }

    /**
     * @param Colour $primaryColour
     * @return CertificateReplacementDraft
     */
    public function setPrimaryColour($primaryColour)
    {
        $this->primaryColour = $primaryColour;
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
     * @param string $reasonForReplacement
     * @return CertificateReplacementDraft
     */
    public function setReasonForReplacement($reasonForReplacement)
    {
        $this->reasonForReplacement = $reasonForReplacement;
        return $this;
    }

    /**
     * @return Colour
     */
    public function getSecondaryColour()
    {
        return $this->secondaryColour;
    }

    /**
     * @param Colour $secondaryColour
     * @return CertificateReplacementDraft
     */
    public function setSecondaryColour($secondaryColour)
    {
        $this->secondaryColour = $secondaryColour;
        return $this;
    }

    /**
     * @return Site
     */
    public function getVehicleTestingStation()
    {
        return $this->vehicleTestingStation;
    }

    /**
     * @param Site $vehicleTestingStation
     * @return CertificateReplacementDraft
     */
    public function setVehicleTestingStation($vehicleTestingStation)
    {
        $this->vehicleTestingStation = $vehicleTestingStation;
        return $this;
    }

    /**
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * @param string $vrm
     * @return CertificateReplacementDraft
     */
    public function setVrm($vrm)
    {
        $this->vrm = $vrm;
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
     * @param string $vin
     * @return CertificateReplacementDraft
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
        return $this;
    }
}
