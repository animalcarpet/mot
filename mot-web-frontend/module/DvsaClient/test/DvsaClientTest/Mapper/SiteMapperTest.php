<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class SiteMapperTest.
 */
class SiteMapperTest extends AbstractMapperTest
{
    const ORGANISATION_ID = 1;

    /** @var $mapper SiteMapper */
    private $mapper;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new SiteMapper($this->client);
    }

    public function testGetById()
    {
        $this->client->expects($this->once())
            ->method('get')
            ->willReturn(['data' => DtoHydrator::dtoToJson((new VehicleTestingStationDto())->setName('Site1'))]);

        $this->assertSame(
            'Site1',
            $this->mapper->getById(self::ORGANISATION_ID)->getName()
        );
    }

    public function testCreate()
    {
        $dto = (new VehicleTestingStationDto());

        $this->client->expects($this->once())
            ->method('post')
            ->with(VehicleTestingStationUrlBuilder::vtsById(), DtoHydrator::dtoToJson($dto))
            ->willReturn(['data' => ['id' => self::ORGANISATION_ID]]);

        $this->assertSame(
            ['id' => self::ORGANISATION_ID],
            $this->mapper->create($dto)
        );
    }

    public function testValidate()
    {
        $dto = (new VehicleTestingStationDto())
            ->setIsNeedConfirmation(true);

        $this->client->expects($this->once())
            ->method('post')
            ->with(VehicleTestingStationUrlBuilder::vtsById(), DtoHydrator::dtoToJson($dto))
            ->willReturn(['data' => true]);

        $this->assertTrue($this->mapper->validate($dto));
    }

    public function testUpdate()
    {
        $this->client->expects($this->once())
            ->method('put')
            ->willReturn(['data' => ['id' => self::ORGANISATION_ID]]);

        $this->assertSame(
            self::ORGANISATION_ID,
            $this->mapper->update(self::ORGANISATION_ID, [])
        );
    }

    public function testValidateTestingFacilities()
    {
        $dto = (new VehicleTestingStationDto())
            ->setIsNeedConfirmation(true);

        $this->client->expects($this->once())
            ->method('put')
            ->with(VehicleTestingStationUrlBuilder::updateTestingFacilities(self::ORGANISATION_ID), DtoHydrator::dtoToJson($dto))
            ->willReturn(['data' => true]);

        $this->assertTrue(
            $this->mapper->validateTestingFacilities(self::ORGANISATION_ID, $dto)
        );
    }

    public function testUpdateTestingFacilities()
    {
        $this->client->expects($this->once())
            ->method('put')
            ->with(VehicleTestingStationUrlBuilder::updateTestingFacilities(self::ORGANISATION_ID))
            ->willReturn(['data' => ['success' => true]])
        ;

        $siteDto = (new SiteDto())
            ->setId(self::ORGANISATION_ID)
        ;
        $OptlFacilitTypeDto = (new FacilityTypeDto())
            ->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE)
        ;
        $OptlFacilityDto = (new FacilityDto())
            ->setType($OptlFacilitTypeDto)
            ->setSite($siteDto)
        ;
        $vtsDto = (new VehicleTestingStationDto())
            ->setFacilities([$OptlFacilityDto])
        ;

        $actualResult = $this->mapper->updateTestingFacilities(self::ORGANISATION_ID, $vtsDto);

        $this->assertSame(
            ['data' => ['success' => true]],
            $actualResult
        );
    }

    public function testSaveDefaultBrakeTests()
    {
        $this->client->expects($this->once())
            ->method('put')
            ->willReturn(['data' => ['id' => self::ORGANISATION_ID]]);

        $this->assertNull(
            $this->mapper->saveDefaultBrakeTests(self::ORGANISATION_ID, [])
        );
    }

    public function getSiteAsArray()
    {
        return [
            'name' => 'Site1',
            'address' => [
                'town' => 'Toulouse',
            ],
            'contacts' => [],
            'positions' => [
                [
                    'person' => [],
                    'role' => 1,
                    'status' => 1,
                    'actionedOn' => 1,
                    'id' => 1,
                ],
            ],
            'siteTestingDailySchedule' => [
                [
                    'weekday' => '',
                    'openTime' => null,
                    'closeTime' => null,
                ],
            ],
        ];
    }

    public function testSearch()
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with(VehicleTestingStationUrlBuilder::search(), [])
            ->willReturn(['data' => ['_class' => SiteListDto::class]]);

        $this->assertInstanceOf(
            SiteListDto::class,
            $this->mapper->search([])
        );
    }
}
