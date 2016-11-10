<?php

namespace DvsaElasticSearchTest\Query;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Query\FbQueryMotTest;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\MotTestRepository;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\DateTime;

/**
 * Class FbQueryMotTestTest
 *
 * @package DvsaElasticSearchTest\Query
 */
class FbQueryMotTestTest extends \PHPUnit_Framework_TestCase
{
    const SITE_ID = 9999;

    /** @var  EntityManager */
    private $mockEM;
    /** @var  FbQueryMotTest */
    protected $FbQueryMotTest;
    /** @var MotTestRepository|MockObj */
    protected $mockRepository;

    public function setup()
    {
        $this->FbQueryMotTest = new FbQueryMotTest();

        $this->mockRepository = XMock::of(MotTestRepository::class, ['getMotTestSearchResult', 'getMotTestSearchResultCount', 'getLatestMotTestsBySiteNumber']);

        $this->mockEM = XMock::of(EntityManager::class);
        $this->mockEM->expects($this->once())
            ->method('getRepository')
            ->with(MotTest::class)
            ->willReturn($this->mockRepository);
    }

    public function testFbQueryMotTestExecute()
    {
        $optionalMotTestTypes = [];

        $searchParam = new MotTestSearchParam($this->mockEM);
        $searchParam
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES)
            ->setSiteNumber('V1234')
        ;

        $this->mockRepository->expects($this->once())
            ->method('getMotTestSearchResult')
            ->will($this->returnValue($this->getMotTestEntities()));

        $this->mockRepository->expects($this->once())
            ->method('getMotTestSearchResultCount')
            ->will($this->returnValue(1));

        $this->assertEquals($this->getResultFb($searchParam), $this->FbQueryMotTest->execute($searchParam, $optionalMotTestTypes));
    }

    public function testFbQueryMotTestExecuteRecent()
    {
        $optionalMotTestTypes = [];

        $searchParam = new MotTestSearchParam($this->mockEM);
        $searchParam
            ->setSearchRecent(true)
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES)
            ->setSiteNumber('V1234')
        ;

        $this->mockRepository->expects($this->once())
            ->method('getLatestMotTestsBySiteNumber')
            ->will($this->returnValue($this->getMotTestEntities()));

        $this->assertEquals(
            $this->getResultFb($searchParam),
            $this->FbQueryMotTest->execute($searchParam, $optionalMotTestTypes)
        );
    }

    protected function getMotTestSearchParams()
    {
        return [
            'siteNumber' => 'V1234',
        ];
    }

    protected function getMotTestEntities()
    {
        $motTest = new MotTest();
        $motTest->setStatus($this->createMotTestActiveStatus());

        $make = new Make();
        $make->setName('Porshe');

        $model = new Model();
        $model->setName('911 Turbo');

        $vehicle = new Vehicle();
        $vehicle->setId(1);

        $tester = new Person();
        $tester->setId(1);
        $tester->setUsername('ft-catb');

        $colour = new Colour();
        $colour->setName('Blue');

        $site = new Site();
        $site
            ->setId(self::SITE_ID)
            ->setSiteNumber('V1234');

        $type = new MotTestType();
        $type->setDescription("Normal Test");
        $type->setCode('NT');

        $motTest->setId(1)
            ->setRegistration('FNZ 6JZ')
            ->setMake($make)
            ->setModel($model)
            ->setVin('hdh7htref0gr5greh')
            ->setVehicle($vehicle)
            ->setMotTestType($type)
            ->setTester($tester)
            ->setHasRegistration(true)
            ->setStartedDate(null)
            ->setPrimaryColour($colour)
            ->setVehicleTestingStation($site)
            ->setNumber('123456789012')
            ->setCompletedDate(null)
            ->setStartedDate(null);

        $results[] = $motTest;

        return $results;
    }

    protected function getResultFb(MotTestSearchParam $searchParams)
    {
        $result = new SearchResultDto();
        $result
            ->setIsElasticSearch(false)
            ->setTotalResultCount(1)
            ->setResultCount(1)
            ->setSearched($searchParams->toDto())
            ->setData([
                '123456789012' => [
                    'motTestNumber'       => '123456789012',
                    'status'              => 'ACTIVE',
                    'primaryColour'       => 'Blue',
                    'hasRegistration'     => 1,
                    'odometer'            => 'Not recorded',
                    'vin'                 => 'hdh7htref0gr5greh',
                    'registration'        => 'FNZ 6JZ',
                    'make'                => 'Porshe',
                    'model'               => '911 Turbo',
                    'testType'            => 'Normal Test',
                    'siteId'              => self::SITE_ID,
                    'siteNumber'          => 'V1234',
                    'testDate'            => null,
                    'startedDate'         => null,
                    'completedDate'       => null,
                    'testerUsername'      => 'ft-catb',
                    'reasonsForRejection' => null
                ]
            ]);

        return $result;
    }

    private function createMotTestActiveStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn(MotTestStatusName::ACTIVE);

        return $status;
    }
}
