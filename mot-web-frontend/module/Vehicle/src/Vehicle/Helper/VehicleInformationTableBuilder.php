<?php

namespace Vehicle\Helper;

use Application\Service\CatalogService;
use Core\ViewModel\Gds\Table\GdsRow;
use Core\ViewModel\Gds\Table\GdsTable;
use DateTime;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommon\Dto\Vehicle\VehicleExpiryDto;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Enum\CountryOfRegistrationCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class VehicleInformationTableBuilder implements AutoWireableInterface
{
    const EMPTY_VALUE_TEXT = 'Unknown';
    const COLOUR_NOT_STATED = 'Not Stated';

    protected static $unknownCountriesCodes = [
        CountryOfRegistrationCode::NON_EU,
        CountryOfRegistrationCode::NOT_APPLICABLE,
        CountryOfRegistrationCode::NOT_KNOWN,
    ];

    private $catalogService;

    /** @var DvsaVehicle */
    private $vehicle;

    /** @var \DvsaCommon\Dto\Vehicle\VehicleExpiryDto */
    private $expiryDateInformation;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * @param DvsaVehicle $vehicle
     * @return VehicleInformationTableBuilder
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    /**
     * @param VehicleExpiryDto $expiryDateInformation
     * @return VehicleInformationTableBuilder
     */
    public function setExpiryDateInformation($expiryDateInformation)
    {
        $this->expiryDateInformation = $expiryDateInformation;
        return $this;
    }

    /**
     * @return GdsTable
     */
    public function getVehicleSpecificationGdsTable()
    {
        $table = new GdsTable();
        $this->addRowToTable($table, 'Make and model', $this->getMakeAndModel());
        $this->addRowToTable($table, 'Engine', $this->getEngineInfo());
        $this->addRowToTable($table, 'Colour', $this->getVehicleColourNames());
        $this->addRowToTable($table, 'Brake test weight', $this->getVehicleBrakeWeight());
        $this->addRowToTable($table, 'MOT test class', $this->vehicle->getVehicleClass());
        $this->addRowToTable($table, 'MOT expiry date', $this->getExpiryDate());

        return $table;
    }

    /**
     * @return GdsTable
     */
    public function getVehicleRegistrationGdsTable()
    {
        $table = new GdsTable();
        $this->addRowToTable($table, 'Registration mark', $this->vehicle->getRegistration());
        $this->addRowToTable($table, 'VIN', $this->vehicle->getVin());
        $this->addRowToTable($table, 'Country of registration', $this->getCountryCodeByName($this->vehicle->getCountryOfRegistration()));
        $this->addRowToTable($table, 'Declared new', $this->vehicle->getIsNewAtFirstReg() ? "Yes" : "No");
        $this->addRowToTable($table, 'Manufacture date', $this->dateFormat($this->vehicle->getManufactureDate()));
        $this->addRowToTable($table, 'First registered', $this->dateFormat($this->vehicle->getFirstRegistrationDate()));
        $this->addRowToTable($table, 'First used', $this->dateFormat($this->vehicle->getFirstUsedDate()));
        $this->addRowToTable($table, 'Details created', $this->dateFormat($this->vehicle->getAmendedOn()));

        return $table;
    }

    /**
     * @param GdsTable $table
     * @param string $label
     * @param string $value
     * @return GdsRow
     */
    private function addRowToTable(GdsTable $table, $label, $value)
    {
        if (is_null($value)) {
            $value = self::EMPTY_VALUE_TEXT;
        }

        $htmlId = str_replace(' ', '-', strtolower($label));
        $gdsRow = $table->newRow($htmlId);
        $gdsRow->setLabel($label)->setValue($value);

        return $gdsRow;
    }

    /**
     * @param string $countryName
     * @return string|null
     */
    private function getCountryCodeByName($countryName)
    {
        $countries = $this->catalogService->getCountriesOfRegistrationByCode();
        $countryCode = array_search($countryName, $countries);

        if (!empty($countryCode)) {
            if (in_array($countryCode, self::$unknownCountriesCodes)) {
                return $countryName;
            }

            return $countryCode;
        }

        return null;
    }

    /**
     * @param DateTime|string $date
     * @return null|string
     */
    private function dateFormat($date)
    {
        if (!empty($date)) {
            $dateObj = $date instanceof DateTime ? $date : new DateTime($date);
            return DateTimeDisplayFormat::date($dateObj);
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    private function getVehicleColourNames()
    {
        if (self::COLOUR_NOT_STATED == $this->vehicle->getColourSecondary()) {
            return $this->vehicle->getColour();
        } else {
            return $this->vehicle->getColour() . ' and ' . $this->vehicle->getColourSecondary();
        }
    }

    /**
     * @return null|string
     */
    private function getExpiryDate()
    {
        if ($this->expiryDateInformation->getPreviousCertificateExists()) {
            return $this->dateFormat($this->expiryDateInformation->getExpiryDate());
        } else {
            return null;
        }
    }


    /**
     * @return string
     */
    private function getMakeAndModel()
    {
        return $this->vehicle->getModel()
            ? $this->vehicle->getMake() . ', ' . $this->vehicle->getModel()
            : $this->vehicle->getMake();
    }

    /**
     * @return int|null
     */
    private function getVehicleBrakeWeight()
    {
        $vehicleWeight = $this->vehicle->getWeight();

        if (!is_null($vehicleWeight)) {
            $vehicleWeight = number_format($vehicleWeight) . ' Kg';
        }
        return $vehicleWeight;
    }

    /**
     * @return string
     */
    private function getEngineInfo()
    {
        $fuelType = $this->vehicle->getFuelType();
        $cylinderCapacity = $this->vehicle->getCylinderCapacity();

        return !is_null($cylinderCapacity)
            ? $fuelType . ', ' . number_format($cylinderCapacity) . ' cc'
            : $fuelType;
    }
}