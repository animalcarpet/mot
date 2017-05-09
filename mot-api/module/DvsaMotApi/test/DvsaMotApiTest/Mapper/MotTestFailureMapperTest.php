<?php

namespace DvsaMotApiTest\Mapper;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Common\ReasonForCancelDto;
use DvsaCommon\Dto\Common\ReasonForRefusalDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaMotApi\Mapper\AbstractMotTestMapper;
use DvsaMotApi\Mapper\MotTestFailureMapper;
use PHPUnit_Framework_TestCase;

/**
 * Mot Test Failure Mapper Tests.
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MotTestFailureMapperTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestFailureMapper */
    private $mapper;

    private $catalog;

    public function setUp()
    {
        $this->catalog = XMock::of(DataCatalogService::class);
        $this->mapper = new MotTestFailureMapper($this->catalog);
    }

    /**
     * Test map data for certificate.
     *
     * @param array $data
     * @param array $additionalData
     * @param bool  $isDualLanguage
     * @param bool  $isNormalTest
     * @param array $expected
     *
     * @dataProvider dataProviderTestMapDataForFailure
     */
    public function testMapDataForFailure($data, $additionalData, $isDualLanguage, $isNormalTest, $expected)
    {
        $this->mapper->setDualLanguage($isDualLanguage);
        $this->mapper->addDataSource('MotTestData', $data);
        $this->mapper->addDataSource('Additional', $additionalData);
        $this->mapper->setNormalTest($isNormalTest);

        $results = $this->mapper->mapData();

        $this->assertEquals($expected, $results);
        $this->assertEquals(
            $expected,
            $results,
            sprintf(
                str_repeat(PHP_EOL, 6).
                ' Expected: [%s] '.PHP_EOL.' Supplied: [%s], json expected: [%s], json supplied: [%s]',
                var_export($expected, true), var_export($results, true), json_encode($expected), json_encode($results)
            )
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataProviderTestMapDataForFailure()
    {
        $reasonForCancel = new ReasonForCancelDto();
        $reasonForCancel
            ->setReason('Some Reason')
            ->setReasonCy('Some Reason Welsh');

        $reasonForRefusal = new ReasonForRefusalDto();
        $reasonForRefusal
            ->setReason('Some Reason')
            ->setReasonCy('Some Reason Welsh');

        $VtsAddress1 = new Address();
        $VtsAddress1->setAddressLine1('ABC Street');
        $VtsAddress1->setAddressLine2('Some Place');
        $VtsAddress1->setTown('Town');

        return [
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Black'),
                    'secondaryColour' => (new ColourDto())->setName('Yellow'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber' => '123456_5',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',

                    ],
                    'reasonForCancel' => $reasonForRefusal,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some 1 comment',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some 3 comment',
                            ],
                            [
                                'name' => 'ABS',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.2',
                                'locationLateral' => 'Pos1',
                                'locationLongitudinal' => 'pos2',
                                'locationVertical' => 'pos3',
                                'comment' => 'Some 2 comment',
                            ],
                        ],
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
                    'OdometerReadings' => [
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
                    ],
                ],
                false,
                false,
                [
                    'TestNumber' => '123456_5',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some 3 comment) [1.1.3]
002 Failed Pos1 pos2 pos3 (Some 2 comment) [1.1.2]',
                    'AdvisoryInformation' => '003 Pos1 pos2 (Some 1 comment)',
                    'Odometer' => '10000 mi',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January 2012',
                    'ReasonForCancel' => 'Some Reason',
                    'ReasonForCancelComment' => 'Some Comment',
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
                    'motTestNumber' => '123456_6',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForRefusal,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'comment' => 'Some comment',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'failureText' => 'Failed',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'inspectionManualReference' => '1.1.12',
                                'comment' => 'Some 1 comment',
                            ],
                            [
                                'name' => 'ABS',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.13',
                                'comment' => 'Some 3 comment',
                            ],
                        ],
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
                    'OdometerReadings' => [
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
                    ],
                ],
                false,
                false,
                [
                    'TestNumber' => '123456_6',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some 1 comment) [1.1.12]'.PHP_EOL.
                        '002 Failed (Some 3 comment) [1.1.13]',
                    'AdvisoryInformation' => '003 (Some comment)',
                    'Odometer' => '10000 mi',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January 2012',
                    'ReasonForCancel' => 'Some Reason',
                    'ReasonForCancelComment' => 'Some Comment',
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
                    'motTestNumber' => '123456_7',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                        ],
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
                    'odometerValue' => null,
                    'odometerUnit' => null,
                    'odometerResultType' => OdometerReadingResultType::NOT_READABLE,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => [
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
                    ],
                ],
                false,
                false,
                [
                    'TestNumber' => '123456_7',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed [1.1.1]'.PHP_EOL.
                        '002 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]',
                    'AdvisoryInformation' => '003 Pos1 pos2 pos3 (Some comment)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_READABLE,
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January 2012',
                    'ReasonForCancel' => 'Some Reason',
                    'ReasonForCancelComment' => 'Some Comment',
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
                    'motTestNumber' => '123456_8',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some 2 comment',
                            ],
                        ],
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
                    'OdometerReadings' => [
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

                    ],
                ],
                false,
                false,
                [
                    'TestNumber' => '123456_8',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        '002 Failed Pos1 pos2 pos3 (Some 2 comment) [1.1.1]',
                    'AdvisoryInformation' => '003 Pos1 pos2 pos3',
                    'Odometer' => '8888 mi',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January 2012',
                    'ReasonForCancel' => 'Some Reason',
                    'ReasonForCancelComment' => 'Some Comment',
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
                    'motTestNumber' => '123456_9',
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForRefusal,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos1',
                                'locationLongitudinal' => 'pos2',
                                'comment' => 'Some comment',
                                'failureDangerous' => 1,
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'locationVerticalCy' => 'pos3Cy',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some 2 comment',
                                'failureDangerous' => 1,
                            ],
                        ],
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
                    'odometerValue' => null,
                    'odometerUnit' => null,
                    'odometerResultType' => OdometerReadingResultType::NO_ODOMETER,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => [
                        [
                            'issuedDate' => new \DateTime('2014-01-01'),
                            'value' => null,
                            'unit' => null,
                            'resultType' => OdometerReadingResultType::NO_ODOMETER,
                        ],
                        [
                            'issuedDate' => new \DateTime('2013-01-01'),
                            'value' => 8000,
                            'unit' => 'mi',
                            'resultType' => OdometerReadingResultType::OK,
                        ],
                    ],
                ],
                true,
                false,
                [
                    'TestNumber' => '123456_9',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Black and Yellow',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '001 FailedCy Pos1 pos2 pos3 (Some comment) [1.1.1] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '002 Failed Pos1 pos2 pos3 (Some 2 comment) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '002 FailedCy Pos1 pos2 pos3 (Some 2 comment) [1.1.1] * PERYGLUS *',
                    'AdvisoryInformation' => '003 pos2 pos1 (Some comment) * DANGEROUS *',
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER.'/'.
                        AbstractMotTestMapper::TEXT_NO_ODOMETER_CY,
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January/Ionawr 2012',
                    'ReasonForCancel' => 'Some Reason / Some Reason Welsh',
                    'ReasonForCancelComment' => 'Some Comment',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Blue'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber' => '123456_10',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'Failed Cy',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'Failed Cy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'Failed Cy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment2',
                                'failureDangerous' => 1,
                            ],
                        ],
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
                    'odometerValue' => null,
                    'odometerUnit' => null,
                    'odometerResultType' => null,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => null,
                ],
                true,
                false,
                [
                    'TestNumber' => '123456_10',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Blue',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment2) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '001 Failed Cy Pos1 pos2 pos3 (Some comment2) [1.1.1] * PERYGLUS *',
                    'AdvisoryInformation' => '002 Pos1 pos2 pos3 (Some comment)'.PHP_EOL.
                        '003 Failed Pos1 pos2 pos3 (Some dangerous) [1.1.4] * DANGEROUS *'.PHP_EOL.
                        '003 Failed Cy Pos1 pos2 pos3 (Some dangerous) [1.1.4] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '004 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        '004 Failed Cy Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        PHP_EOL.
                        '005 Pos1 pos2 pos3 (Some blah blah)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_RECORDED.'/'.
                        AbstractMotTestMapper::TEXT_NOT_RECORDED_CY,
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January/Ionawr 2012',
                    'ReasonForCancel' => 'Some Reason / Some Reason Welsh',
                    'ReasonForCancelComment' => 'Some Comment',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Blue'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber' => '123456_11',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment2',
                                'failureDangerous' => 1,
                            ],
                        ],
                        'PRS' => [
                            [
                                'type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2110,
                                'rfrId' => 8455,
                                'name' => 'Body condition',
                                'nameCy' => 'Body conditionCy',
                                'failureText' => 'has a sharp edge caused by corrosion',
                                'failureTextCy' => 'ymyl miniog a achoswyd gan gyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                            [
                                'type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2112,
                                'rfrId' => 8457,
                                'name' => 'Body condition',
                                'nameCy' => '',
                                'failureText' => 'has a projection caused by corrosion',
                                'failureTextCy' => 'bargodiad peryglus oherwydd cyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                        ],

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
                    'OdometerReadings' => [
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
                    ],
                ],
                false,
                false,
                [
                    'TestNumber' => '123456_11',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Blue',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment2) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '002 has a sharp edge caused by corrosion [6.1.C.1]'.PHP_EOL.
                        '003 has a projection caused by corrosion [6.1.C.1]',
                    'AdvisoryInformation' => '004 Pos1 pos2 pos3 (Some comment)'.PHP_EOL.
                        '005 Failed Pos1 pos2 pos3 (Some dangerous) [1.1.4] * DANGEROUS *'.PHP_EOL.
                        '006 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        '007 Pos1 pos2 pos3 (Some blah blah)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER,
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January 2012',
                    'ReasonForCancel' => 'Some Reason',
                    'ReasonForCancelComment' => 'Some Comment',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Blue'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber' => '123456_12',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V1234',
                        'name' => 'Some Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment2',
                                'failureDangerous' => 1,
                            ],
                        ],
                        'PRS' => [
                            [
                                'type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2110,
                                'rfrId' => 8455,
                                'name' => 'Body condition',
                                'nameCy' => 'Body conditionCy',
                                'failureText' => 'has a sharp edge caused by corrosion',
                                'failureTextCy' => 'ymyl miniog a achoswyd gan gyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                            [
                                'type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2112,
                                'rfrId' => 8457,
                                'name' => 'Body condition',
                                'nameCy' => '',
                                'failureText' => 'has a projection caused by corrosion',
                                'failureTextCy' => 'bargodiad peryglus oherwydd cyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                        ],

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
                    'odometerValue' => null,
                    'odometerUnit' => null,
                    'odometerResultType' => OdometerReadingResultType::NOT_READABLE,
                ],
                [
                    'TestStationAddress' => $VtsAddress1,
                    'OdometerReadings' => [
                        [
                            'issuedDate' => new \DateTime('2014-01-01'),
                            'value' => 10000,
                            'unit' => 'mi',
                            'result_type' => OdometerReadingResultType::NO_ODOMETER,
                        ],
                        [
                            'issuedDate' => new \DateTime('2013-01-01'),
                            'value' => 8000,
                            'unit' => 'mi',
                            'result_type' => OdometerReadingResultType::OK,
                        ],
                    ],
                ],
                true,
                false,
                [
                    'TestNumber' => '123456_12',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'Colour' => 'Blue',
                    'InspectionAuthority' => "Some Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment2) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '001 FailedCy Pos1 pos2 pos3 (Some comment2) [1.1.1] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '002 has a sharp edge caused by corrosion [6.1.C.1]'.PHP_EOL.
                        '002 ymyl miniog a achoswyd gan gyrydu [6.1.C.1]'.PHP_EOL.
                        PHP_EOL.
                        '003 has a projection caused by corrosion [6.1.C.1]'.PHP_EOL.
                        '003 bargodiad peryglus oherwydd cyrydu [6.1.C.1]',
                    'AdvisoryInformation' => '004 Pos1 pos2 pos3 (Some comment)'.PHP_EOL.
                        '005 Failed Pos1 pos2 pos3 (Some dangerous) [1.1.4] * DANGEROUS *'.PHP_EOL.
                        '005 FailedCy Pos1 pos2 pos3 (Some dangerous) [1.1.4] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '006 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        '006 FailedCy Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        PHP_EOL.
                        '007 Pos1 pos2 pos3 (Some blah blah)',
                    'Odometer' => AbstractMotTestMapper::TEXT_NOT_READABLE.'/'.
                        AbstractMotTestMapper::TEXT_NOT_READABLE_CY,
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'TestStation' => 'V1234',
                    'FirstUseDate' => '1 January/Ionawr 2012',
                    'ReasonForCancel' => 'Some Reason / Some Reason Welsh',
                    'ReasonForCancelComment' => 'Some Comment',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Blue'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber' => '123456_13',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V123542',
                        'name' => 'Welsh Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment2',
                                'failureDangerous' => 1,
                            ],
                        ],

                        'PRS' => [
                            ['type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2110,
                                'rfrId' => 8455,
                                'name' => 'Body condition',
                                'nameCy' => '',
                                'failureText' => 'has a sharp edge caused by corrosion',
                                'failureTextCy' => 'ymyl miniog a achoswyd gan gyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                            [
                                'type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2112,
                                'rfrId' => 8457,
                                'name' => 'Body condition',
                                'nameCy' => '',
                                'failureText' => 'has a projection caused by corrosion',
                                'failureTextCy' => 'bargodiad peryglus oherwydd cyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                        ],

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
                    'OdometerReadings' => [
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
                    ],
                ],
                false,
                true,
                [
                    'TestNumber' => '123456_13',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'Colour' => 'Blue',
                    'TestStation' => 'V123542',
                    'InspectionAuthority' => "Welsh Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER,
                    'IssuedDate' => '1 Jan 2014',
                    'IssuersName' => 'B. Tester',
                    'FirstUseDate' => '1 January 2012',
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment2) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '002 has a sharp edge caused by corrosion [6.1.C.1]'.PHP_EOL.
                        '003 has a projection caused by corrosion [6.1.C.1]'.PHP_EOL.
                        PHP_EOL.
                        'For retest procedures and details of free retest items please refer to the MOT fees and appeals poster at the testing station or alternatively the details can be found at www.gov.uk/getting-an-mot/retests'
                        .PHP_EOL,
                    'AdvisoryInformation' => '004 Pos1 pos2 pos3 (Some comment)'.PHP_EOL.
                        '005 Failed Pos1 pos2 pos3 (Some dangerous) [1.1.4] * DANGEROUS *'.PHP_EOL.
                        '006 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        '007 Pos1 pos2 pos3 (Some blah blah)',
                    'ReasonForCancel' => 'Some Reason',
                    'ReasonForCancelComment' => 'Some Comment',
                ],
            ],
            [
                [
                    'primaryColour' => (new ColourDto())->setName('Blue'),
                    'secondaryColour' => (new ColourDto())->setCode(ColourCode::NOT_STATED)->setName('No Other Colour'),
                    'countryOfRegistration' => (new CountryDto())->setName('UK'),
                    'registration' => 'AB15 ADS',
                    'vin' => 'BJS45646',
                    'make' => 'German',
                    'model' => 'Whip',
                    'motTestNumber' => '123456_14',
                    'vehicle' => (new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')),
                    'vehicleTestingStation' => [
                        'siteNumber' => 'V123542',
                        'name' => 'Welsh Garage',
                        'primaryTelephone' => '011712013243',
                    ],
                    'reasonForCancel' => $reasonForCancel,
                    'reasonForTerminationComment' => 'Some Comment',
                    'reasonsForRejection' => [
                        'ADVISORY' => [
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.4',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some dangerous',
                                'failureDangerous' => 1,
                            ],
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment',
                            ],
                            [
                                'name' => 'Manual Advisory',
                                'locationVertical' => 'pos3',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some blah blah',
                            ],
                        ],
                        'FAIL' => [
                            [
                                'name' => 'ABS',
                                'locationVertical' => 'pos3',
                                'failureText' => 'Failed',
                                'failureTextCy' => 'FailedCy',
                                'inspectionManualReference' => '1.1.1',
                                'locationLongitudinal' => 'pos2',
                                'locationLateral' => 'Pos1',
                                'comment' => 'Some comment2',
                                'failureDangerous' => 1,
                            ],
                        ],

                        'PRS' => [
                            ['type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2110,
                                'rfrId' => 8455,
                                'name' => 'Body condition',
                                'nameCy' => 'Body condCy',
                                'failureText' => 'has a sharp edge caused by corrosion',
                                'failureTextCy' => 'ymyl miniog a achoswyd gan gyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                            [
                                'type' => 'PRS',
                                'locationLateral' => null,
                                'locationLongitudinal' => null,
                                'locationVertical' => null,
                                'comment' => null,
                                'failureDangerous' => false,
                                'generated' => false,
                                'customDescription' => null,
                                'onOriginalTest' => false,
                                'id' => 2112,
                                'rfrId' => 8457,
                                'name' => 'Body condition',
                                'nameCy' => 'Body conditionCy',
                                'failureText' => 'has a projection caused by corrosion',
                                'failureTextCy' => 'bargodiad peryglus oherwydd cyrydu',
                                'testItemSelectorId' => 5696,
                                'inspectionManualReference' => '6.1.C.1',
                            ],
                        ],

                    ],
                    'issuedDate' => '2014-01-01',
                    'tester' => [
                        'displayName' => 'B. Tester',
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
                    'OdometerReadings' => [
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
                    ],
                ],
                true,
                true,
                [
                    'TestNumber' => '123456_14',
                    'VRM' => 'AB15 ADS',
                    'VIN' => 'BJS45646',
                    'Make' => 'German',
                    'Model' => 'Whip',
                    'CountryOfRegistration' => 'UK',
                    'TestClass' => VehicleClassCode::CLASS_4,
                    'Colour' => 'Blue',
                    'TestStation' => 'V123542',
                    'InspectionAuthority' => "Welsh Garage\nABC Street\nSome Place\nTown\t\t011712013243\n",
                    'Odometer' => AbstractMotTestMapper::TEXT_NO_ODOMETER.'/'.
                        AbstractMotTestMapper::TEXT_NO_ODOMETER_CY,
                    'IssuedDate' => '1 Jan/Ion 2014',
                    'IssuersName' => 'B. Tester',
                    'FirstUseDate' => '1 January/Ionawr 2012',
                    'FailureInformation' => '001 Failed Pos1 pos2 pos3 (Some comment2) [1.1.1] * DANGEROUS *'.PHP_EOL.
                        '001 FailedCy Pos1 pos2 pos3 (Some comment2) [1.1.1] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '002 has a sharp edge caused by corrosion [6.1.C.1]'.PHP_EOL.
                        '002 ymyl miniog a achoswyd gan gyrydu [6.1.C.1]'.PHP_EOL.
                        PHP_EOL.
                        '003 has a projection caused by corrosion [6.1.C.1]'.PHP_EOL.
                        '003 bargodiad peryglus oherwydd cyrydu [6.1.C.1]'.PHP_EOL.
                        PHP_EOL.
                        'For retest procedures and details of free retest items please refer to the MOT fees and '.
                        'appeals poster at the testing station or alternatively the details can be found at '.
                        'www.gov.uk/getting-an-mot/retests'.
                        PHP_EOL.
                        PHP_EOL.
                        'Ar gyfer rheolau ailbrofi ac manylion o eitemau ailbrofi am ddim, gwelwch y poster ffioedd '.
                        'ac apelau MOT yn y gorsaf brofi neu darganfyddwch y manylion ar '.
                        'www.gov.uk/getting-an-mot/retests'.
                        PHP_EOL,
                    'AdvisoryInformation' => '004 Pos1 pos2 pos3 (Some comment)'.PHP_EOL.
                        '005 Failed Pos1 pos2 pos3 (Some dangerous) [1.1.4] * DANGEROUS *'.PHP_EOL.
                        '005 FailedCy Pos1 pos2 pos3 (Some dangerous) [1.1.4] * PERYGLUS *'.PHP_EOL.
                        PHP_EOL.
                        '006 Failed Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        '006 FailedCy Pos1 pos2 pos3 (Some comment) [1.1.1]'.PHP_EOL.
                        PHP_EOL.
                        '007 Pos1 pos2 pos3 (Some blah blah)',
                    'ReasonForCancel' => 'Some Reason / Some Reason Welsh',
                    'ReasonForCancelComment' => 'Some Comment',
                ],
            ],
        ];
    }
}
