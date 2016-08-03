<?php

namespace DvsaMotApiTest\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Dto\Vehicle\ModelDetailDto;
use DvsaCommon\Dto\Vehicle\ModelDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Mapper\OdometerReadingMapper;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaCommon\Dto\Common\OdometerReadingDTO;
use DvsaMotApi\Mapper\AbstractMotTestMapper;
use DvsaMotApi\Mapper\MotTestCertificateMapper;
use PHPUnit_Framework_TestCase;

/**
 * Mot Test Certificate Mapper Tests
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MotTestCertificateMapperTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestCertificateMapper mapper */
    private $mapper;

    private $catalog;

    public function setUp()
    {
        $this->catalog = XMock::of(DataCatalogService::class);
        $this->mapper = new MotTestCertificateMapper($this->catalog);
    }

    /**
     * Test map data for certificate
     *
     * @param array $data
     * @param array $additionalData
     * @param bool $dualLanguage
     * @param array $expected
     *
     * @dataProvider mapDataForCertificateProvider
     */
    public function testMapDataForCertificate($data, $additionalData, $dualLanguage, $expected)
    {
        $this->mapper->setDualLanguage($dualLanguage);
        $this->mapper->addDataSource('MotTestData', $data);
        $this->mapper->addDataSource('Additional', $additionalData);

        $results = $this->mapper->mapData();

        $this->assertEquals($expected, $results);
    }

    public function testVM4443FixUsesOdometerValueIfPresent()
    {
        $testData = $this->mapDataForCertificateProvider();
        $aTest = $testData[0];

        $aTest[0]['odometerReading'] = (new OdometerReadingDTO())
            ->setUnit('mi')
            ->setValue(-1)
            ->setResultType(OdometerReadingResultType::OK); // break it so we know *we* changed it here
        $this->mapper->setDualLanguage($aTest[2]);
        $this->mapper->addDataSource('MotTestData', $aTest[0]);

        $aTest[3]['Odometer'] = "-1 mi";     // set the new expectation for the check
        $this->mapper->addDataSource('Additional', $aTest[1]);

        $results = $this->mapper->mapData();
        $this->assertEquals($aTest[3], $results);
    }

    public function testVM4443FixUsesOdometerReadingsArrayWhenNoOdometerValueIsPresent()
    {
        $testData = $this->mapDataForCertificateProvider();
        $aTest = $testData[0];

        $aTest[0]['odometerReading'] = null; // break it so we know *we* changed it here
        $this->mapper->setDualLanguage($aTest[2]);
        $this->mapper->addDataSource('MotTestData', $aTest[0]);

        // remove the Odometer reading so it has to use OdometerReadings[]
        $aTest[1]['OdometerReadings'][0] = (new OdometerReadingDTO())
            ->setIssuedDate('2014-01-29')
            ->setUnit('mi')
            ->setValue(31415927)
            ->setResultType(OdometerReadingResultType::OK); // set datum value
        $aTest[3]['Odometer'] = "31415927 mi";     // set the new expectation for the check
        $aTest[3]['OdometerHistory'] =
            '29 1 2014: 31415927 mi' . PHP_EOL .     // set the new expectation for the check
            '13 12 2011: 8000 mi';

        $this->mapper->addDataSource('Additional', $aTest[1]);

        $results = $this->mapper->mapData();
        $this->assertEquals($aTest[3], $results);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function mapDataForCertificateProvider()
    {
        $VtsAddress1 = new Address();
        $VtsAddress1->setAddressLine1('ABC Street');
        $VtsAddress1->setAddressLine2('Some Place');
        $VtsAddress1->setTown('Town');

        $odometerReadingMapper = new OdometerReadingMapper();

        return [
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Blue'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'  => '134564_1',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment'
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment'
                            ],
                        ],
                    ],
                    'expiryDate' => '2015-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => OdometerReadingResultType::OK,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => '2014-01-29',
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                            [
                                'issuedDate' => '2011-12-13',
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                false,
                [
                    'TestNumber' => '134564_1',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Blue',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 Pos1 pos2 pos3 (Some comment)
002 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]',
                    'Odometer' => '10000 mi',
                    'OdometerHistory' =>
                        '29 1 2014: 10000 mi' . PHP_EOL .
                        '13 12 2011: 8000 mi',
                    'ExpiryDate' => '1 January 2015 (FIFTEEN)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December 2014.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'         => '134564_2',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [

                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment'
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment'
                            ],
                        ],
                    ],
                    'expiryDate' => '2015-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => OdometerReadingResultType::OK,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                            [
                                'issuedDate' => new \DateTime('2011-12-13'),
                                'value'      => null,
                                'unit'       => null,
                                'resultType' => OdometerReadingResultType::NO_ODOMETER,
                            ],
                        ]
                    ),
                ],
                false,
                [
                    'TestNumber' => '134564_2',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' =>
                        '001 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]' . PHP_EOL .
                        '002 Pos1 pos2 pos3 (Some comment)',
                    'Odometer' => '10000 mi',
                    'OdometerHistory' =>
                        '1 1 2014: 10000 mi' . PHP_EOL .
                        '13 12 2011: ' . AbstractMotTestMapper::TEXT_NO_ODOMETER,
                    'ExpiryDate' => '1 January 2015 (FIFTEEN)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December 2014.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'                => '134564_3',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'comment' => 'Some comment'
                            ],
                        ],
                    ],
                    'expiryDate' => '2015-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => OdometerReadingResultType::NOT_READABLE,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::NOT_READABLE,
                            ],
                            [
                                'issuedDate' => new \DateTime('2011-12-13'),
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                false,
                [
                    'TestNumber' => '134564_3',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' =>
                            '001 Failed pos2 pos3 [1.1.1]' . PHP_EOL .
                            '002 pos2 pos3 (Some comment)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_READABLE,
                    'OdometerHistory' =>
                        '1 1 2014: '. AbstractMotTestMapper::TEXT_NOT_READABLE . PHP_EOL .
                        '13 12 2011: 8000 mi',
                    'ExpiryDate' => '1 January 2015 (FIFTEEN)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December 2014.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'                => '134564_4',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment'
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment'
                            ],
                        ],
                    ],
                    'expiryDate' => '2015-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => odometerReadingResultType::NO_ODOMETER,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => odometerReadingResultType::NO_ODOMETER,
                            ],
                            [
                                'issuedDate' => new \DateTime('2011-12-13'),
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                false,
                [
                    'TestNumber' => '134564_4',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos2 pos1 (Some comment)
002 Failed pos3 pos2 pos1 (Some comment) [1.1.1]',
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER,
                    'OdometerHistory' =>
                        '1 1 2014: '. AbstractMotTestMapper::TEXT_NO_ODOMETER . PHP_EOL .
                        '13 12 2011: 8000 mi',
                    'ExpiryDate' => '1 January 2015 (FIFTEEN)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December 2014.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'         => '134564_5',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                        ],
                    ],
                    'expiryDate' => '2099-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => odometerReadingResultType::NO_ODOMETER,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => odometerReadingResultType::NO_ODOMETER,
                            ],
                            [
                                'issuedDate' => new \DateTime('2013-12-11'),
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                false,
                [
                    'TestNumber' => '134564_5',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos2 pos1 (Some comment)' . PHP_EOL .
                        '002 Failed pos3 pos2 pos1 (Some comment) [1.1.1]',
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER,
                    'OdometerHistory' =>
                        '1 1 2014: '. AbstractMotTestMapper::TEXT_NO_ODOMETER . PHP_EOL .
                        '11 12 2013: 8000 mi',
                    'ExpiryDate' => '1 January 2099 (NINETY-NINE)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December 2098.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'                => '134564_6',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                    ],
                    'expiryDate' => '2099-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value'      => null,
                                'unit'       => null,
                                'resultType' => odometerReadingResultType::NOT_READABLE,
                            ],
                            [
                                'issuedDate' => new \DateTime('2013-12-11'),
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                false,
                [
                    'TestNumber' => '134564_6',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos2 pos1 (Some comment)
002 Failed pos3 pos2 pos1 (Some dangerous) [1.1.4] * DANGEROUS *
003 Failed pos3 pos2 pos1 (Some comment) [1.1.1]
004 pos3 pos2 pos1 (Some blah blah)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_READABLE,
                    'OdometerHistory' =>
                        '1 1 2014: '. AbstractMotTestMapper::TEXT_NOT_READABLE . PHP_EOL .
                        '11 12 2013: 8000 mi',
                    'ExpiryDate' => '1 January 2099 (NINETY-NINE)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December 2098.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Orange'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'                => '134564_7',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'dualLanguage' => true,
                        'primaryTelephone' => '011712013243'
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'ABS',
                                'nameCy' => 'ABS(W)',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'Failed(W)',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment'
                            ],
                        ],
                    ],
                    'expiryDate' => '2015-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => OdometerReadingResultType::OK,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-29'),
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                            [
                                'issuedDate' => new \DateTime('2011-12-13'),
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                true,
                [
                    'TestNumber' => '134564_7',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Orange',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 Failed pos3 pos2 pos1 (Some comment) [1.1.1]' . PHP_EOL
                        . '001 Failed(W) pos3 pos2 pos1 (Some comment) [1.1.1]',
                    'Odometer' => '10000 mi',
                    'OdometerHistory' =>
                        '29 1 2014: 10000 mi' . PHP_EOL .
                        '13 12 2011: 8000 mi',
                    'ExpiryDate' => '1 January/Ionawr 2015' . PHP_EOL . '(FIFTEEN / UN DEG PUMP)',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December/Rhagfyr 2014.',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber'                => '134564_8',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                // @NOTE: deliberately blank to test fallback logic
                                'nameCy' => '',
                                'failureTextCy' => '',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'nameCy' => '(W)',
                                'failureTextCy' => 'Failed (W)',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                    ],
                    'expiryDate' => '2099-01-01',
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerReading' => $odometerReadingMapper->toDtoFromArray(
                        [
                            'value'      => 10000,
                            'unit'       => 'mi',
                            'resultType' => odometerReadingResultType::NO_ODOMETER,
                        ]
                    ),
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value'      => 10000,
                                'unit'       => 'mi',
                                'resultType' => odometerReadingResultType::NO_ODOMETER,
                            ],
                            [
                                'issuedDate' => new \DateTime('2013-12-11'),
                                'value'      => 8000,
                                'unit'       => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                true,
                [
                    'TestNumber'            => '134564_8',
                    'VRM'                   => 'AB15 ADS',
                    'VIN'                   => 'BJS45646',
                    'Make'                  => 'German',
                    'Model'                 => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour'                => 'Black and Yellow',
                    'InspectionAuthority'   => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    // @NOTE: we don't expect manual advisories to repeat, since there's no translation
                    'AdvisoryInformation'   => '001 pos3 pos2 pos1 (Some comment)' . PHP_EOL
                        . '002 Failed pos3 pos2 pos1 (Some dangerous) [1.1.4] * DANGEROUS *' . PHP_EOL
                        . '002 Failed pos3 pos2 pos1 (Some dangerous) [1.1.4] * PERYGLUS *' . PHP_EOL . PHP_EOL
                        . '003 Failed pos3 pos2 pos1 (Some comment) [1.1.1]' . PHP_EOL
                        . '003 Failed (W) pos3 pos2 pos1 (Some comment) [1.1.1]' . PHP_EOL . PHP_EOL
                        . '004 pos3 pos2 pos1 (Some blah blah)',
                    'Odometer'              => AbstractMotTestMapper::TEXT_NO_ODOMETER . '/' .
                        AbstractMotTestMapper::TEXT_NO_ODOMETER_CY,
                    'OdometerHistory'       =>
                        '1 1 2014: ' . AbstractMotTestMapper::TEXT_NO_ODOMETER . '/' .
                        AbstractMotTestMapper::TEXT_NO_ODOMETER_CY . PHP_EOL .
                        '11 12 2013: 8000 mi',
                    'ExpiryDate'            => '1 January/Ionawr 2099' . PHP_EOL . '(NINETY-NINE / NAW DEG NAW)',
                    'IssuedDate'            => '1 Jan/Ion 2014',
                    'IssuersName'           => 'B. Tester',
                    'TestStation'           => 'V1234',
                    'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can'
                        . ' present your vehicle for test is 2 December/Rhagfyr 2098.',
                    'TestClass' => VehicleClassCode::CLASS_4,
                ],
            ],
        ];
    }
}
