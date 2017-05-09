<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\VehicleExpiryMapper;
use DvsaCommon\Dto\Vehicle\VehicleExpiryDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommonTest\TestUtils\TestCaseTrait;

class VehicleExpiryMapperTest extends AbstractMapperTest
{
    const EXPIRY_DATE = '2014-02-01';
    const EARLIEST_DATE = '2013-10-10';
    use TestCaseTrait;

    const VEHICLE_ID = 1;

    /** @var VehicleExpiryMapper */
    private $mapper;

    public function setUp()
    {
        parent::setUp();
        $this->mapper = new VehicleExpiryMapper($this->client, new DtoReflectiveDeserializer());
    }

    public function testFetchAllForManager()
    {
        $this->setupClientMockGet(
            VehicleUrlBuilder::testExpiryCheck(self::VEHICLE_ID, false),
            [
                'data' => [
                    'checkResult' => [
                        'earliestTestDateForPostdatingExpiryDate' => self::EARLIEST_DATE,
                        'expiryDate' => self::EXPIRY_DATE,
                        'isEarlierThanTestDateLimit' => false,
                        'previousCertificateExists' => true,
                    ],
                ],
            ]
        );

        $vehicleExpiryDto = $this->mapper->getExpiryForVehicle(self::VEHICLE_ID);

        $this->assertInstanceOf(
            VehicleExpiryDto::class,
            $vehicleExpiryDto
        );

        $this->assertEquals($vehicleExpiryDto->getExpiryDate(), (new \DateTime(self::EXPIRY_DATE)));
        $this->assertEquals($vehicleExpiryDto->getEarliestTestDateForPostdatingExpiryDate(), (new \DateTime(self::EARLIEST_DATE)));
        $this->assertEquals($vehicleExpiryDto->getIsEarlierThanTestDateLimit(), false);
        $this->assertEquals($vehicleExpiryDto->getPreviousCertificateExists(), true);
    }
}
