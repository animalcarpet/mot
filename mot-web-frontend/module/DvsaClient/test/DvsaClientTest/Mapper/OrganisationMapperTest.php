<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\OrganisationMapper;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\TestUtils\TestCaseTrait;

/**
 * Class OrganisationMapperTest.
 */
class OrganisationMapperTest extends AbstractMapperTest
{
    use TestCaseTrait;

    const AE_ID = 1;
    const AE_NUMBER = 'A-12345';

    /**
     * @var OrganisationMapper
     */
    private $mapper;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new OrganisationMapper($this->client);
    }

    public function testFetchAllForManager()
    {
        $this->setupClientMockGet(
            PersonUrlBuilder::byId(self::AE_ID)->authorisedExaminer(),
            ['data' => ['_class' => OrganisationDto::class]]
        );

        $this->assertInstanceOf(
            OrganisationDto::class,
            $this->mapper->fetchAllForManager(self::AE_ID)
        );
    }

    public function testGetAuthorisedExaminer()
    {
        $this->setupClientMockGet(
            AuthorisedExaminerUrlBuilder::of(self::AE_ID),
            ['data' => ['_class' => OrganisationDto::class]]
        );

        $this->assertInstanceOf(
            OrganisationDto::class,
            $this->mapper->getAuthorisedExaminer(self::AE_ID)
        );
    }

    public function testGetAuthorisedExaminerByNumber()
    {
        $this->mockMethod(
            $this->client,
            'getWithParams',
            $this->any(),
            ['data' => ['_class' => OrganisationDto::class]],
            [AuthorisedExaminerUrlBuilder::of()->authorisedExaminerByNumber(), self::AE_NUMBER]
        );

        $this->assertInstanceOf(
            OrganisationDto::class,
            $this->mapper->getAuthorisedExaminerByNumber(self::AE_NUMBER)
        );
    }

    public function testUpdateAuthorisedExaminer()
    {
        $expectDto = new OrganisationDto();
        $expect = ['id' => self::AE_ID];

        $this->setupClientMockPut(
            AuthorisedExaminerUrlBuilder::of(self::AE_ID),
            DtoHydrator::dtoToJson($expectDto),
            ['data' => $expect]
        );

        $actualDto = $this->mapper->update(self::AE_ID, $expectDto);

        $this->assertEquals($expect, $actualDto);
    }

    public function testCreateAuthorisedExaminer()
    {
        $expectDto = new OrganisationDto();
        $expect = 'expect response';

        $this->setupClientMockPost(
            AuthorisedExaminerUrlBuilder::of(),
            DtoHydrator::dtoToJson($expectDto),
            ['data' => $expect]
        );

        $actualDto = $this->mapper->create($expectDto);

        $this->assertEquals($expect, $actualDto);
    }

    public function testValidateAuthorisedExaminer()
    {
        $expectDto = (new OrganisationDto())
            ->setIsValidateOnly(true);
        $expect = 'expect response';

        $this->setupClientMockPost(
            AuthorisedExaminerUrlBuilder::of(),
            DtoHydrator::dtoToJson($expectDto),
            ['data' => $expect]
        );

        $actualDto = $this->mapper->validate($expectDto);

        $this->assertEquals($expect, $actualDto);
    }
}
