<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Helper\ReplacementCertificate;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Date\DateUtils;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Helper\ReplacementCertificate\ReplacementCertificateDraftDiffHelper;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use DvsaMotApiTest\Factory\VehicleObjectsFactory as VOF;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateDraftDiffHelperTest.
 */
class ReplacementCertificateDraftDiffHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider data
     */
    public function testGetDiff_givenDraft_correctDiffReturned($input, $output)
    {
        $draftEntity = self::mapData($input['draft'], $input['test']);
        $diff = ReplacementCertificateDraftDiffHelper::getDiff($draftEntity);

        $this->assertEquals(0, count(array_diff($diff, $output)), 'Incorrect diff detected');
    }

    /**
     * @param array $draftData
     * @param array $testData
     *
     * @return CertificateReplacementDraft
     */
    private function mapData($draftData, $testData)
    {
        $draft = CertificateReplacementDraft::create();
        $isDraftKey = self::isKey($draftData);
        $isTestKey = self::isKey($testData);

        if ($isDraftKey('cor')) {
            $draft->setCountryOfRegistration($draftData['cor']);
        }
        if ($isDraftKey('primaryColour')) {
            $draft->setPrimaryColour($draftData['primaryColour']);
        }
        if ($isDraftKey('secondaryColour')) {
            $draft->setSecondaryColour($draftData['secondaryColour']);
        }
        if ($isDraftKey('expiryDate')) {
            $draft->setExpiryDate($draftData['expiryDate']);
        }
        if ($isDraftKey('vts')) {
            $draft->setVehicleTestingStation($draftData['vts']);
        }
        if ($isDraftKey('vin')) {
            $draft->setVin($draftData['vin']);
        }
        if ($isDraftKey('vrm')) {
            $draft->setVrm($draftData['vrm']);
        }
        if ($isDraftKey('odometerValue')) {
            $draft->setOdometerValue($draftData['odometerValue']);
        }
        if ($isDraftKey('odometerUnit')) {
            $draft->setOdometerUnit($draftData['odometerUnit']);
        }
        if ($isDraftKey('odometerResultType')) {
            $draft->setOdometerResultType($draftData['odometerResultType']);
        }
        if ($isDraftKey('make')) {
            $draft->setMake($draftData['make']);
        }
        if ($isDraftKey('model')) {
            $draft->setModel($draftData['model']);
        }

        $modelDetail = new ModelDetail();

        if ($isTestKey('model')) {
            $model = new Model();
            $model->setName($testData['model']);

            if ($isTestKey('make')) {
                $make = new Make();
                $make->setName($testData['make']);
                $model->setMake($make);
            }

            $modelDetail->setModel($model);
        }

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setVersion(1);

        if ($isTestKey('cor')) {
            $vehicle->setCountryOfRegistration((new CountryOfRegistration())->setName($testData['cor']));
        }

        if ($isTestKey('primaryColour')) {
            $vehicle->setColour($testData['primaryColour']);
        }

        if ($isTestKey('secondaryColour')) {
            $vehicle->setSecondaryColour($testData['secondaryColour']);
        }

        if ($isTestKey('vin')) {
            $vehicle->setVin($testData['vin']);
        }

        if ($isTestKey('vrm')) {
            $vehicle->setRegistration($testData['vrm']);
        }

        $test = new MotTest();
        $test->setVehicle($vehicle);
        $test->setVehicleVersion($vehicle->getVersion());

        if ($isTestKey('expiryDate')) {
            $test->setExpiryDate($testData['expiryDate']);
        }
        if ($isTestKey('vts')) {
            $test->setVehicleTestingStation($testData['vts']);
        }
        if ($isTestKey('odometerValue')) {
            $test->setOdometerValue($testData['odometerValue']);
        }
        if ($isTestKey('odometerUnit')) {
            $test->setOdometerUnit($testData['odometerUnit']);
        }
        if ($isTestKey('odometerResultType')) {
            $test->setOdometerResultType($testData['odometerResultType']);
        }

        return $draft->setMotTest($test);
    }

    public static function data()
    {
        return [
            [
                'input' => [
                    'draft' => [
                        'odometerValue' => 11,
                        'odometerUnit' => OdometerUnit::KILOMETERS,
                        'odometerResultType' => OdometerReadingResultType::OK,
                        'primaryColour' => VOF::colour(1),
                    ],
                    'test' => [
                        'odometerValue' => 11,
                        'odometerUnit' => OdometerUnit::MILES,
                        'odometerResultType' => OdometerReadingResultType::OK,
                        'primaryColour' => VOF::colour(1),
                    ],
                ],
                'output' => [
                    'odometerReading',
                ],
            ],
            [
                'input' => [
                    'draft' => [
                        'odometerValue' => 11,
                        'odometerUnit' => OdometerUnit::MILES,
                        'odometerResultType' => OdometerReadingResultType::OK,
                        'primaryColour' => VOF::colour(1),
                    ],
                    'test' => [
                        'odometerValue' => 11,
                        'odometerUnit' => OdometerUnit::MILES,
                        'odometerResultType' => OdometerReadingResultType::OK,
                        'primaryColour' => VOF::colour(1),
                    ],
                ],
                'output' => [
                    'odometerReading',
                ],
            ],
            [
                'input' => [
                    'draft' => [
                        'cor' => VOF::countryOfRegistration(1),
                        'primaryColour' => VOF::colour(1),
                        'secondaryColour' => VOF::colour(2),
                        'expiryDate' => DateUtils::toDate('2014-05-01'),
                        'make' => VOF::make(1),
                        'model' => VOF::model(1),
                        'vts' => MotTestObjectsFactory::vts(5),
                        'vrm' => 'VRM',
                        'vin' => 'VIN',
                        'odometerValue' => 12,
                        'odometerUnit' => OdometerUnit::KILOMETERS,
                        'odometerResultType' => OdometerReadingResultType::OK,
                    ],
                    'test' => [
                        'cor' => VOF::countryOfRegistration(2),
                        'primaryColour' => VOF::colour(11),
                        'secondaryColour' => VOF::colour(22),
                        'expiryDate' => DateUtils::toDate('2014-05-02'),
                        'make' => VOF::make(2),
                        'model' => VOF::make(2),
                        'vts' => MotTestObjectsFactory::vts(55),
                        'vrm' => 'XXX',
                        'vin' => 'YYY',
                        'odometerValue' => 1212,
                        'odometerUnit' => OdometerUnit::KILOMETERS,
                        'odometerResultType' => OdometerReadingResultType::OK,
                    ],
                ],
                'output' => [
                    'countryOfRegistration',
                    'primaryColour',
                    'secondaryColour',
                    'make',
                    'model',
                    'expiryDate',
                    'vehicleTestingStation',
                    'odometerReading',
                    'registration',
                    'vin',
                ],
            ],
        ];
    }

    private static function isKey(&$array)
    {
        return function ($key) use (&$array) {
            return array_key_exists($key, $array);
        };
    }
}
