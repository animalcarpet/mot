<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\MotTest;
use DvsaEntitiesTest\DqlBuilder\BuildMotTestSearchParamsTrait;

/**
 * Class MotTestSearchParamTest.
 */
class MotTestSearchParamTest extends AbstractServiceTestCase
{
    use BuildMotTestSearchParamsTrait;

    protected $mockEm;

    public function setup()
    {
        $this->mockEm = XMock::of(EntityManager::class, ['getRepository']);
    }

    /**
     * Test we can create a MotTestSearchParam.
     */
    public function testSearchParamDefaultsAndFluidInterface()
    {
        $params = $this->buildMotTestSearchParamWithSiteNumber($this->mockEm, 'V1234')
            ->setSortColumnId(2)
            ->setSortDirection('ASC')
            ->setSearchFilter('filter')
            ->setVin('ABCDEF')
            ->setRegistration('FEDCBA')
            ->setVehicleId(1)
            ->setSearchRecent(true)
            ->setDateFrom(new \DateTime('2014-03-02'))
            ->setDateTo(new \DateTime('2013-02-0'))
            ->setRowCount(10)
            ->setTestNumber('999999999990134')
            ->setStart(50);

        $this->assertInstanceOf(MotTestSearchParam::class, $params);

        $this->assertEquals('V1234', $params->getSiteNumber());
        $this->assertEquals('999999999990134', $params->getTestNumber());
        $this->assertEquals(2, $params->getSortColumnId());
        $this->assertEquals('status', $params->getSortColumnName());
        $this->assertEquals('ASC', $params->getSortDirection());
        $this->assertEquals(10, $params->getRowCount());
        $this->assertEquals(50, $params->getStart());
        $this->assertEquals('ABCDEF', $params->getVin());
        $this->assertEquals('FEDCBA', $params->getRegistration());
        $this->assertEquals(1, $params->getVehicleId());
        $this->assertEquals('filter', $params->getSearchFilter());
        $this->assertEquals(true, $params->getSearchRecent());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Invalid search. One of site number, tester, vehicle, vrm or vin id must be passed.
     */
    public function testInvalidInputsNoParams()
    {
        (new MotTestSearchParam($this->mockEm))->process();
    }

    public function testSearchSiteNumberToArray()
    {
        $params = $this->buildMotTestSearchParamWithSiteNumber($this->mockEm, 'V1234')
            ->setRowCount(100)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC)
            ->setStart(25);

        $values = $params->toArray();

        $this->assertCount(17, $values);
        $this->assertEquals('V1234', $values['siteNumber']);
        $this->assertEquals(0, $values['siteId']);
        $this->assertEquals(null, $values['testerId']);
        $this->assertEquals(null, $values['testNumber']);
        $this->assertEquals(0, $values['sortColumnId']);
        $this->assertEquals('testDate', $values['sortColumnName']);
        $this->assertEquals(SearchParamConst::SORT_DIRECTION_DESC, $values['sortDirection']);
        $this->assertEquals(100, $values['rowCount']);
        $this->assertEquals(25, $values['start']);
    }

    public function testTestUserIdNumberToArray()
    {
        $params = $this->buildMotTestSearchParamWithTesterId($this->mockEm, '105')
            ->setRowCount(100)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC)
            ->setStart(25);

        $values = $params->toArray();

        $this->assertCount(17, $values);
        $this->assertEquals(0, $values['siteId']);
        $this->assertEquals(null, $values['siteNumber']);
        $this->assertEquals(null, $values['testNumber']);
        $this->assertEquals('105', $values['testerId']);
        $this->assertEquals(0, $values['sortColumnId']);
        $this->assertEquals('testDate', $values['sortColumnName']);
        $this->assertEquals(SearchParamConst::SORT_DIRECTION_DESC, $values['sortDirection']);
        $this->assertEquals(100, $values['rowCount']);
        $this->assertEquals(25, $values['start']);
    }

    public function testSearchColumnsWhenToArray()
    {
        // available as MotTestSearchParam::$dbSortByColumns but want to fix the tests
        // at a point in time, independent of that array

        $searchColumns = [
            '0' => 'testDate', // mot_test
            '2' => 'status', // mot_test
            '3' => 'vin', // vehicle
            '4' => 'registration', // vehicle
            '5' => 'startedDate', // mot_test
            '6' => 'make', // make
            '7' => 'model', // model
            '8' => 'testType', // testType
            '9' => 'siteNumber', // vts
            '10' => 'testerUsername', // tester -> user
            '11' => 'testNumber',
        ];

        foreach ($searchColumns as $id => $name) {
            $params = $this->buildMotTestSearchParamWithTesterId($this->mockEm, '105')
                ->setSortColumnId($id);

            $values = $params->toArray();

            $this->assertEquals($id, $values['sortColumnId'], "column should be {$id} for {$name}");
            $this->assertEquals($name, $values['sortColumnName'], "column should be {$name} for {$id}");
        }
    }

    public function testFromDto()
    {
        $dto = new MotTestSearchParamsDto();

        $dto
            ->setDateFromTs((new \DateTime('1974-11-08'))->getTimestamp())
            ->setDateToTs((new \DateTime())->getTimestamp())
            ->setOrganisationId(999)
            ->setPersonId(8888)
            ->setSiteNr('V5555')
            ->setTestNumber('99999999999999013')
            ->setStatus([MotTestStatusName::ABANDONED, MotTestStatusName::PASSED])
            ->setTestType([MotTestTypeCode::MOT_COMPLIANCE_SURVEY, MotTestTypeCode::NON_MOT_TEST])
            ->setVehicleId(7777)
            ->setVehicleRegNr('ABC 1238')
            ->setVehicleVin('ASDFGHJKL1');

        $obj = new MotTestSearchParam($this->mockEm);
        $obj->fromDto($dto);

        $this->assertEquals($dto->getOrganisationId(), $obj->getOrganisationId());
        $this->assertEquals($dto->getSiteNr(), $obj->getSiteNumber());
        $this->assertEquals($dto->getTestNumber(), $obj->getTestNumber());
        $this->assertEquals($dto->getPersonId(), $obj->getTesterId());
        $this->assertEquals($dto->getVehicleId(), $obj->getVehicleId());
        $this->assertEquals($dto->getVehicleRegNr(), $obj->getRegistration());
        $this->assertEquals($dto->getVehicleVin(), $obj->getVin());
        $this->assertEquals($dto->getDateFromTs(), $obj->getDateFrom()->getTimestamp());
        $this->assertEquals($dto->getDateToTs(), $obj->getDateTo()->getTimestamp());
        $this->assertEquals($dto->getStatus(), $obj->getStatus());
        $this->assertEquals($dto->getTestType(), $obj->getTestType());
    }

    public function testToDto()
    {
        $obj = new MotTestSearchParam($this->getMockEntityManager());
        $obj
            ->setSiteNumber('V1234')
            ->setStatus([MotTestStatusName::ABANDONED, MotTestStatusName::PASSED])
            ->setTesterId(9999)
            ->setTestType([MotTestTypeCode::MOT_COMPLIANCE_SURVEY])
            ->setTestNumber('99999999999999013')
            ->setVehicleId(8888)
            ->setRegistration('AAA BBB2')
            ->setVin('ABCD1234')
            ->setOrganisationId(7777)
            ->setDateFrom(new \DateTime('1974-11-08'))
            ->setDateTo(new \DateTime())
        ;

        $dto = new MotTestSearchParamsDto();
        $obj->toDto($dto);

        $this->assertEquals($dto->getOrganisationId(), $obj->getOrganisationId());
        $this->assertEquals($dto->getSiteNr(), $obj->getSiteNumber());
        $this->assertEquals($dto->getPersonId(), $obj->getTesterId());
        $this->assertEquals($dto->getVehicleId(), $obj->getVehicleId());
        $this->assertEquals($dto->getVehicleVin(), $obj->getVin());
        $this->assertEquals($dto->getVehicleRegNr(), $obj->getRegistration());
        $this->assertEquals($dto->getDateFromTs(), $obj->getDateFrom()->getTimestamp());
        $this->assertEquals($dto->getDateToTs(), $obj->getDateTo()->getTimestamp());
        $this->assertEquals($dto->getStatus(), $obj->getStatus());
        $this->assertEquals($dto->getTestType(), $obj->getTestType());
        $this->assertEquals($dto->getTestNumber(), $obj->getTestNumber());
    }

    public function testGetRepository()
    {
        $mockEntityManager = $this->getMockEntityManager();
        $mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(MotTest::class)
            ->willReturn('RepositoryObject');

        $searchParams = new MotTestSearchParam($mockEntityManager);

        $repo = $searchParams->getRepository();

        $this->assertEquals($repo, 'RepositoryObject');
    }
}
