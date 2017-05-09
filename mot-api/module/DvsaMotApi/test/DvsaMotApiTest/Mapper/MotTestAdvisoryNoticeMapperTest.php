<?php

namespace DvsaMotApiTest\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Mapper\OdometerReadingMapper;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Mapper\AbstractMotTestMapper;
use PHPUnit_Framework_TestCase;
use DvsaMotApi\Mapper\MotTestAdvisoryNoticeMapper;
use DvsaEntities\Entity\Address;

/**
 * Mot Test Advisory Notice Mapper Tests.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MotTestAdvisoryNoticeMapperTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestAdvisoryNoticeMapper */
    private $mapper;

    private $catalog;

    public function setUp()
    {
        $this->catalog = XMock::of(DataCatalogService::class);
        $this->mapper = new MotTestAdvisoryNoticeMapper($this->catalog);
    }

    /**
     * Test map data for advisory notice.
     *
     * @param array $data
     * @param array $additionalData
     * @param bool  $dualLanguage
     * @param array $expected
     *
     * @dataProvider mapDataProvider
     */
    public function testMapData($data, $additionalData, $dualLanguage, $expected)
    {
        $this->mapper->setDualLanguage($dualLanguage);
        $this->mapper->addDataSource('MotTestData', $data);
        $this->mapper->addDataSource('Additional', $additionalData);

        $results = $this->mapper->mapData();
        $this->assertEquals($expected, $results);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function mapDataProvider()
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
                    'motTestNumber' => '134564_1',
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
                    'expiryDate' => [
                        'date' => '2015-01-01',
                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerValue' => 8888,
                    'odometerUnit' => 'mi',
                    'odometerResultType' => OdometerReadingResultType::OK,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => [
                                    'date' => '2014-01-01',
                                ],
                                'value' => 10000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                            [
                                'issuedDate' => [
                                    'date' => '2013-01-01',
                                ],
                                'value' => 8000,
                                'unit' => 'mi',
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
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos2 pos1 (Some comment)
002 Failed pos3 pos2 pos1 (Some comment) [1.1.1]',
                    'Odometer' => '8888 mi',
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Blue',
                    'TestClass' => VehicleClassCode::CLASS_4,
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'motTestNumber' => '134564_2',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'make' => 'German',
                    'model' => 'Whip',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some 1 comment',
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
                    'expiryDate' => [
                        'date' => '2015-01-01',
                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerValue' => 99999,
                    'odometerUnit' => 'mi',
                    'odometerResultType' => OdometerReadingResultType::OK,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => [
                                    'date' => '2014-01-01',
                                ],
                                'value' => 99999,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                            [
                                'issuedDate' => [
                                    'date' => '2013-01-01',
                                ],
                                'value' => 8000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
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
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos2 pos1 (Some 1 comment)
002 Failed pos3 pos2 pos1 (Some comment) [1.1.1]',
                    'Odometer' => '99999 mi',
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'TestClass' => VehicleClassCode::CLASS_4,
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
                    'motTestNumber' => '134564_3',
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
                            ],
                        ],
                    ],
                    'expiryDate' => [
                        'date' => '2015-01-01',
                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerValue' => 99999,
                    'odometerUnit' => 'mi',
                    'odometerResultType' => OdometerReadingResultType::NOT_READABLE,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => [
                                    'date' => '2014-01-01',
                                ],
                                'value' => 10000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::NOT_READABLE,
                            ],
                            [
                                'issuedDate' => [
                                    'date' => '2013-01-01',
                                ],
                                'value' => 8000,
                                'unit' => 'mi',
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
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos2 pos1 (Some comment)
002 Failed pos3 pos2 pos1 [1.1.1]',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_READABLE,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'TestClass' => VehicleClassCode::CLASS_4,
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
                    'motTestNumber' => '134564_4',
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
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'comment' => 'Some  2 comment',
                            ],
                        ],
                    ],
                    'expiryDate' => [
                        'date' => '2015-01-01',
                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerValue' => 10000,
                    'odometerUnit' => 'mi',
                    'odometerResultType' => OdometerReadingResultType::OK,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => [
                                    'date' => '2014-01-01',
                                ],
                                'value' => 10000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::NO_ODOMETER,
                            ],
                            [
                                'issuedDate' => [
                                    'date' => '2013-01-01',
                                ],
                                'value' => 8000,
                                'unit' => 'mi',
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
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 pos3 pos1 (Some comment)
002 Failed pos2 pos1 (Some  2 comment) [1.1.1]',
                    'Odometer' => '10000 mi',
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'TestClass' => VehicleClassCode::CLASS_4,
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
                    'motTestNumber' => '134564_5',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                            ],
                            [
                                'name' => 'Manual Advisory',
                            ],
                        ],
                    ],
                    'expiryDate' => [
                        'date' => '2099-01-01',
                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerValue' => 10000,
                    'odometerUnit' => 'mi',
                    'odometerResultType' => OdometerReadingResultType::NOT_READABLE,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value' => 10000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::NOT_READABLE,
                            ],
                            [
                                'issuedDate' => new \DateTime('2013-01-01'),
                                'value' => 8000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                true,
                [
                    'TestNumber' => '134564_5',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 Failed pos1 [1.1.1]'.PHP_EOL.
                        '001 FailedCy pos1 [1.1.1]'.PHP_EOL.
                        PHP_EOL.
                        '002 ',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_READABLE.'/'.
                        AbstractMotTestMapper::TEXT_NOT_READABLE_CY,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'TestClass' => VehicleClassCode::CLASS_4,
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
                    'motTestNumber' => '134564_6',
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonsForRejection' => [
                        'ADVISORY' => [

                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'manadv Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos1',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationLateral' => 'pos3',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                    ],
                    'expiryDate' => [
                        'date' => '2099-01-01',
                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'Bob Tester',
                        'firstName' => 'Bob',
                        'middleName' => '',
                        'familyName' => 'Tester',
                    ],
                    'vehicleClass' => (new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4)
                        ->setName(VehicleClassCode::CLASS_4),
                    'odometerValue' => 10000,
                    'odometerUnit' => 'mi',
                    'odometerResultType' => OdometerReadingResultType::NO_ODOMETER,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => $odometerReadingMapper->manyToDtoFromArray(
                        [
                            [
                                'issuedDate' => new \DateTime('2014-01-01'),
                                'value' => 10000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::NO_ODOMETER,
                            ],
                            [
                                'issuedDate' => new \DateTime('2013-01-01'),
                                'value' => 8000,
                                'unit' => 'mi',
                                'resultType' => OdometerReadingResultType::OK,
                            ],
                        ]
                    ),
                ],
                true,
                [
                    'TestNumber' => '134564_6',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'AdvisoryInformation' => '001 Failed pos3 pos2 pos1 (Some dangerous) [1.1.4] * DANGEROUS *'.PHP_EOL.
                        '001 FailedCy pos3 pos2 pos1 (Some dangerous) [1.1.4] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '002 pos3 pos2 (manadv Some comment)'.PHP_EOL.
                        '003 Failed pos3 pos2 pos1 (Some comment) [1.1.1]'.PHP_EOL.
                        '003 FailedCy pos3 pos2 pos1 (Some comment) [1.1.1]'.PHP_EOL.
                        PHP_EOL.
                        '004 pos3 (Some blah blah)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER.'/'.
                        AbstractMotTestMapper::TEXT_NO_ODOMETER_CY,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'TestClass' => VehicleClassCode::CLASS_4,
                ],
            ],
        ];
    }
}
