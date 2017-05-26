<?php

namespace DvsaMotApiTest\Factory;

use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle as DvlaImportedVehicle;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\VehicleClassId;
use DvsaEntities\Entity\BodyType;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\TransmissionType;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Entity\WeightSource;

/**
 * Class VehicleObjectsFactory.
 */
class VehicleObjectsFactory
{
    const EXAMPLE_VIN = '1HGCM82633A004352';
    const EXAMPLE_VRM = 'YK02OML';

    public static function make($id = 4, $code = 'BB', $name = 'Mini')
    {
        return (new Make())->setId($id)->setCode($code)->setName($name);
    }

    public static function model($id = 5, $code = 'COOPER', $name = 'Cooper', $make = null)
    {
        if (!$make) {
            $make = self::make();
        }

        return (new Model())->setId($id)->setCode($code)->setName($name)->setMake($make);
    }

    public static function countryOfRegistration($id = 1, $name = 'UK')
    {
        return (new CountryOfRegistration())->setId($id)->setName($name);
    }

    public static function colour($id = 11, $code = 'R', $name = 'Red')
    {
        return (new Colour())->setId($id)->setCode($code)->setName($name);
    }

    public static function vehicleClass(
        $id = VehicleClassId::CLASS_4,
        $code = VehicleClassCode::CLASS_4,
        $name = VehicleClassCode::CLASS_4
    ) {
        return (new VehicleClass($code, $name))->setId($id);
    }

    /**
     * @param int $id
     *
     * @return Vehicle
     */
    public static function vehicle($id = 1)
    {
        $modelDetail = new ModelDetail();
        $modelDetail->setCylinderCapacity(123)
            ->setFuelType(self::fuelType())
            ->setModel(self::model())
            ->setBodyType(self::bodyType())
            ->setVehicleClass(self::vehicleClass())
            ->setTransmissionType(self::transmissionType());

        return (new Vehicle())->setId($id)
            ->setColour(self::colour(1, 'R', 'Red'))
            ->setSecondaryColour(self::colour(2, 'G', 'Green'))
            ->setModelDetail($modelDetail)
            ->setManufactureDate(DateUtils::toDate('2004-04-23'))
            ->setFirstRegistrationDate(DateUtils::toDate('2007-08-09'))
            ->setFirstUsedDate(DateUtils::toDate('2000-12-12'))
            ->setCountryOfRegistration(self::countryOfRegistration())
            ->setYear(2000)
            ->setRegistration(self::EXAMPLE_VRM)
            ->setVin(self::EXAMPLE_VIN);
    }

    /**
     * @param int $id
     *
     * @return DvlaVehicle
     */
    public static function dvlaVehicle($id = 1)
    {
        $dvlaVehicle = new DvlaVehicle();

        $dvlaVehicle
            ->setId($id)
            ->setPrimaryColour('G')
            ->setSecondaryColour('R')
            ->setCylinderCapacity(123)
            ->setManufactureDate(DateUtils::toDate('2000-12-12'))
            ->setFirstRegistrationDate(DateUtils::toDate('2000-12-12'))
            ->setFuelType('PE')
            ->setMake(self::make())
            ->setModel(self::model())
            ->setBodyType('SE')
            ->setRegistration(self::EXAMPLE_VRM)
            ->setVin(self::EXAMPLE_VIN)
            ->setUnladenWeight(1000)
            ->setDesignedGrossWeight(1200)
            ->setMassInServiceWeight(1100)
            ->setV5DocumentNumber(123456)
            ->setDvlaVehicleId(2);

        return $dvlaVehicle;
    }

    /**
     * this is new vehicle model as the result of Java vehicle-service.
     *
     * @param int $id
     *
     * @return VehicleFromDvla
     */
    public static function dvlaImportedVehicle($id = 1)
    {
        $dvlaVehicleData = json_decode(
            json_encode(
                [
                    'id' => 2,
                    'amendedOn' => '2016-02-03',
                    'registration' => 'DII4454',
                    'vin' => '1M7GDM9AXKP042777',
                    'emptyVrmReason' => null,
                    'emptyVinReason' => null,
                    'make' => 'PORSCHE',
                    'model' => 'BOXSTER',
                    'colour' => [
                        'code' => 'C',
                        'name' => 'Red',
                    ],
                    'colourSecondary' => [
                        'code' => 'W',
                        'name' => 'Not Stated',
                    ],
                    'vehicleClass' => ['code' => '4', 'name' => '4'],
                    'fuelType' => [
                        'code' => FuelTypeCode::PETROL,
                        'name' => 'Petrol',
                    ],
                    'bodyType' => '2 Door Saloon',
                    'cylinderCapacity' => 1700,
                    'transmissionType' => 'Automatic',
                    'firstRegistrationDate' => '2001-03-02',
                    'firstUsedDate' => '2001-03-02',
                    'manufactureDate' => '2001-03-02',
                    'isNewAtFirstReg' => false,
                    'weight' => null,
                ]
            )
        );
        $dvlaVehicle = new DvlaImportedVehicle($dvlaVehicleData);

        return $dvlaVehicle;
    }

    public static function modelDetail($id = 1, $name = 'Standard', $code = 'STD')
    {
        $model = new Model();
        $model->setId($id)
            ->setName($name)
            ->setCode($code);

        return (new ModelDetail())->setModel($model);
    }

    public static function transmissionType($id = 1, $name = 'Manual', $code = 'M')
    {
        return (new TransmissionType())->setId($id)->setName($name)->setCode($code);
    }

    public static function fuelType($id = 1, $code = 'PE', $name = 'Petrole')
    {
        return (new FuelType())->setId($id)->setCode($code)->setName($name);
    }

    public static function bodyType($id = '1', $code = 'SE', $name = 'Sedan')
    {
        return (new BodyType())->setId($id)->setCode($code)->setName($name);
    }

    /**
     * @param string $code
     *
     * @return WeightSource
     */
    public static function weightSource($code)
    {
        return (new WeightSource())->setCode($code);
    }
}
