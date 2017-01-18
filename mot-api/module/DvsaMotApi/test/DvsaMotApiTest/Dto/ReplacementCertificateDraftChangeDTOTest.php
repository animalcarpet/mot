<?php

namespace DvsaMotApiTest\Dto;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;
use PHPUnit_Framework_TestCase;

/**
 * Class ReplacementCertificateDraftChangeDTOTest
 */
class ReplacementCertificateDraftChangeDTOTest extends PHPUnit_Framework_TestCase
{
    public function testFromDataArrayGivenArrayAsInputShouldMapDataCorrectly()
    {
        $data = [
            'primaryColour'             => 1,
            'secondaryColour'           => 2,
            'vin'                       => 'VIN',
            'vrm'                       => 'VRM',
            'expiryDate'                => '2019-12-12',
            'make'                      => 1,
            'model'                     => 2,
            'vtsSiteNumber'             => 'F123A',
            'odometerReading'   => [
                'value'         => 12,
                'unit'          => OdometerUnit::KILOMETERS,
                'resultType'    => OdometerReadingResultType::OK
            ],
            'countryOfRegistration' => 2,
            'reasonForReplacement'      => 'reason1',
            'reasonForDifferentTester'  => 'reason2',
            'customMake' => 'CUSTOMC',
            'customModel' => 'CUSTOMD'
        ];

        $change = ReplacementCertificateDraftChangeDTO::fromDataArray($data);

        $this->assertEquals($data['primaryColour'], $change->getPrimaryColour());
        $this->assertEquals($data['secondaryColour'], $change->getSecondaryColour());
        $this->assertEquals($data['vin'], $change->getVin());
        $this->assertEquals($data['vrm'], $change->getVrm());
        $this->assertEquals($data['expiryDate'], $change->getExpiryDate());
        $this->assertEquals($data['vtsSiteNumber'], $change->getVtsSiteNumber());
        $this->assertEquals($data['countryOfRegistration'], $change->getCountryOfRegistration());
        $this->assertEquals($data['make'], $change->getMake());
        $this->assertEquals($data['model'], $change->getModel());
        $this->assertEquals($data['reasonForReplacement'], $change->getReasonForReplacement());
        $this->assertEquals($data['reasonForDifferentTester'], $change->getReasonForDifferentTester());
        $this->assertEquals($data['odometerReading']['value'], $change->getOdometerValue());
        $this->assertEquals($data['odometerReading']['unit'], $change->getOdometerUnit());
        $this->assertEquals($data['odometerReading']['resultType'], $change->getOdometerResultType());
        $this->assertEquals($data['customMake'], $change->getCustomMake());
        $this->assertEquals($data['customModel'], $change->getCustomModel());
    }
}
