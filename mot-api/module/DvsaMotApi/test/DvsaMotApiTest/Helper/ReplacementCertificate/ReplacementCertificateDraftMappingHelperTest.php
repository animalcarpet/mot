<?php

namespace DvsaMotApiTest\Helper\ReplacementCertificate;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaMotApi\Helper\ReplacementCertificate\ReplacementCertificateDraftMappingHelper;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use DvsaMotApiTest\Factory\VehicleObjectsFactory as VOF;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateDraftMappingHelperTest.
 */
class ReplacementCertificateDraftMappingHelperTest extends PHPUnit_Framework_TestCase
{
    public function testToJsonArray_givenFullRights_returnsAllProperties()
    {
        $draft = $this->buildReplacementCertificateDraft();
        $vts = $draft->getVehicleTestingStation();
        $arr = ReplacementCertificateDraftMappingHelper::toJsonArray($draft, true);
        $eqTable = [
            [
                'countryOfRegistration.id',
                $draft->getCountryOfRegistration()->getId(),
                $arr['countryOfRegistration']['id'],
            ],
            [
                'countryOfRegistration.name',
                $draft->getCountryOfRegistration()->getName(),
                $arr['countryOfRegistration']['name'],
            ],
            ['primaryColour.code', $draft->getPrimaryColour()->getCode(), $arr['primaryColour']['code']],
            ['primaryColour.name', $draft->getPrimaryColour()->getName(), $arr['primaryColour']['name']],
            ['secondaryColour.code', $draft->getSecondaryColour()->getCode(), $arr['secondaryColour']['code']],
            ['secondaryColour.name', $draft->getSecondaryColour()->getName(), $arr['secondaryColour']['name']],
            ['expiryDate', DateTimeApiFormat::date($draft->getExpiryDate()), $arr['expiryDate']],
            ['make.id', $draft->getMake()->getId(), $arr['make']['id']],
            ['make.code', $draft->getMake()->getCode(), $arr['make']['code']],
            ['make.name', $draft->getMake()->getName(), $arr['make']['name']],
            ['model.id', $draft->getModel()->getId(), $arr['model']['id']],
            ['model.code', $draft->getModel()->getCode(), $arr['model']['code']],
            ['model.name', $draft->getModel()->getName(), $arr['model']['name']],
            ['odometerReading.value', $draft->getOdometerValue(), $arr['odometerReading']['value']],
            ['odometerReading.unit', OdometerUnit::MILES, $arr['odometerReading']['unit']],
            ['odometerReading.resultType', OdometerReadingResultType::OK, $arr['odometerReading']['resultType']],
            ['vin', $draft->getVin(), $arr['vin']],
            ['vrm', $draft->getVrm(), $arr['vrm']],
            ['customMake', $draft->getMakeName(), $arr['customMake']],
            ['customModel', $draft->getModelName(), $arr['customModel']],
            ['motTestId', $draft->getMotTest()->getNumber(), $arr['motTestNumber']],
            ['vts.id', $vts->getId(), $arr['vts']['id']],
            ['vts.name', $vts->getName(), $arr['vts']['name']],
            ['vts.siteNumber', $vts->getSiteNumber(), $arr['vts']['siteNumber']],
            ['vts.address.line1', $vts->getAddress()->getAddressLine1(), $arr['vts']['address']['addressLine1']],
            ['vts.address.line2', $vts->getAddress()->getAddressLine2(), $arr['vts']['address']['addressLine2']],
            ['vts.address.line3', $vts->getAddress()->getAddressLine3(), $arr['vts']['address']['addressLine3']],
            ['vts.address.line4', $vts->getAddress()->getAddressLine4(), $arr['vts']['address']['addressLine4']],
            ['vts.address.town', $vts->getAddress()->getTown(), $arr['vts']['address']['town']],
            ['vts.address.country', $vts->getAddress()->getCountry(), $arr['vts']['address']['country']],
            ['vts.address.postcode', $vts->getAddress()->getPostcode(), $arr['vts']['address']['postcode']],
        ];

        foreach ($eqTable as $row) {
            $this->assertEquals($row[1], $row[2], 'Incorrect mapping for: '.$row[0]);
        }
    }

    public function testToJsonArray_givenNotFullRights_returnsSubsetOfPropertiesCorrectly()
    {
        $draft = $this->buildReplacementCertificateDraft();
        $arr = ReplacementCertificateDraftMappingHelper::toJsonArray($draft, false);
        $eqTable = [
            ['primaryColour.code', $draft->getPrimaryColour()->getCode(), $arr['primaryColour']['code']],
            ['primaryColour.name', $draft->getPrimaryColour()->getName(), $arr['primaryColour']['name']],
            ['secondaryColour.code', $draft->getSecondaryColour()->getCode(), $arr['secondaryColour']['code']],
            ['secondaryColour.name', $draft->getSecondaryColour()->getName(), $arr['secondaryColour']['name']],
            ['odometerReading.value', $draft->getOdometerValue(), $arr['odometerReading']['value']],
            ['odometerReading.unit', OdometerUnit::MILES, $arr['odometerReading']['unit']],
            ['odometerReading.resultType', OdometerReadingResultType::OK, $arr['odometerReading']['resultType']],
            ['motTestId', $draft->getMotTest()->getNumber(), $arr['motTestNumber']],
            ['expiryDate', DateTimeApiFormat::date($draft->getExpiryDate()), $arr['expiryDate']],
        ];

        foreach ($eqTable as $row) {
            $this->assertEquals($row[1], $row[2], 'Incorrect mapping for: '.$row[0]);
        }
    }

    public function testToJsonArray_givenNotFullRights_returnsStrictSetOfProperties()
    {
        $draft = $this->buildReplacementCertificateDraft();
        $arr = ReplacementCertificateDraftMappingHelper::toJsonArray($draft, false);
        $allowedProperties = [
            'odometerReading',
            'motTestNumber',
            'primaryColour',
            'secondaryColour',
            'isLatestPassedMotTest',
            'expiryDate',
        ];
        $result = array_intersect_key(array_keys($arr), $allowedProperties);

        $this->assertEquals(
            0,
            count(array_diff($result, $allowedProperties)),
            'Other than specified properties have been returned'
        );
    }

    public function testToJsonArray_givenNoSecondaryColour_returnsSecondaryPropertyAsNull()
    {
        $draft = $this->buildReplacementCertificateDraft()->setSecondaryColour(null);

        $arr = ReplacementCertificateDraftMappingHelper::toJsonArray($draft, false);
        $this->assertNull($arr['secondaryColour'], 'Secondary colour should be returned as null if not defined');
    }

    public function testToJsonArray_givenFullRights_returnsStrictSetOfProperties()
    {
        $draft = $this->buildReplacementCertificateDraft();
        $arr = ReplacementCertificateDraftMappingHelper::toJsonArray($draft, false);
        $allowedProperties = [
            'odometerReading',
            'motTestNumber',
            'primaryColour',
            'secondaryColour',
            'vts',
            'vin',
            'vrm',
            'expiryDate',
            'make',
            'model',
            'countryOfRegistration',
            'isLatestPassedMotTest',
            'expiryDate',
        ];
        $result = array_intersect_key(array_keys($arr), $allowedProperties);
        $this->assertEquals(
            0,
            count(array_diff($result, $allowedProperties)),
            'Other than specified properties have been returned'
        );
    }

    private function buildReplacementCertificateDraft()
    {
        return (new CertificateReplacementDraft())
            ->setCountryOfRegistration(VOF::countryOfRegistration(1, 'cor'))
            ->setPrimaryColour(VOF::colour(2, 'R', 'red'))
            ->setSecondaryColour(VOF::colour(3, 'G', 'green'))
            ->setExpiryDate(DateUtils::toDate('2014-05-01'))
            ->setMake(VOF::make(4, 'BMW', 'BMW'))
            ->setModel(VOF::model(5, 'M3', 'M3'))
            ->setMakeName('TOYOTA UK')
            ->setModelName('SUPRA 3')
            ->setOdometerValue(333)
            ->setOdometerUnit(OdometerUnit::MILES)
            ->setOdometerResultType(OdometerReadingResultType::OK)
            ->setDifferentTesterReason('reasonForDifferentTester')
            ->setReasonForReplacement('reasonForReplacement')
            ->setVin('vin')
            ->setMotTest((new MotTest())->setId(1))
            ->setVrm('vrm')
            ->setVehicleTestingStation(MotTestObjectsFactory::vts(4));
    }
}
