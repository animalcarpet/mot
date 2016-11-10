<?php

namespace DvsaElasticSearchTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaElasticSearchTest\EsHelperTest;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\DqlBuilder\SearchParam\VehicleTestingStationSearchParam;
use DvsaEntities\Repository\SiteRepository;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceManager;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class ElasticSearchServiceTest
 *
 * @package DvsaElasticSearchTest\Service
 */
class ElasticSearchServiceTest extends AbstractServiceTestCase
{
    /** @var  ServiceManager */
    protected $serviceManager;

    protected $mockAuth;
    /** @var  SiteRepository|MockObj */
    protected $mockSiteRepository;
    protected $mockEm;
    /** @var FeatureToggles|MockObj $featureToggleMock */
    protected $featureToggleMock;

    /** @var  ElasticSearchService */
    protected $esService;

    /** @var  \DateTime */
    protected $dateFrom;
    /** @var  \DateTime */
    protected $dateTo;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->mockEm = $this->getMock(EntityManager::class, ['getRepository'], [], '', false);
        $this->mockSiteRepository = XMock::of(SiteRepository::class);
        $this->featureToggleMock = XMock::of(FeatureToggles::class);

        $this->mockAuth = $this->getMockAuthorizationService(true);

        $this->esService = new ElasticSearchService($this->mockAuth, $this->mockSiteRepository, $this->featureToggleMock);

        $this->dateFrom = new \DateTime('1970-01-01');
        $this->dateTo = new \DateTime();

        parent::setUp();
    }


    public function testCanRemoveAnyLockfileWithPassphrase()
    {
        // issues to be addressed:
        //  -- using the configuration file
        //  -- supplying a mocked configuration file
        $this->assertTrue(true);
    }

    protected function getEsQuerySiteReturn()
    {
        $result = EsHelperTest::getDefaultResult();
        $result['hits']['hits'][] = [
            '_index'  => 'development_site',
            '_type'   => 'site',
            '_id'     => '14',
            '_score'  => null,
            '_source' => [
                'siteNumber'   => 'S000001',
                'name'         => 'Mike and John VTS',
                'addressLine1' => '67 Main Road',
                'addressLine2' => null,
                'addressLine3' => null,
                'addressLine4' => null,
                'town'         => 'Bristol',
                'postcode'     => 'BS8 2NT',
                'telephone'    => null,
                'classes'      => [],
                'type'         => 'AREA OFFICE',
                'status'       => 'APPLIED',
            ]
        ];
        return $result;
    }

    protected function getEsQueryTestsReturn()
    {
        $result = EsHelperTest::getDefaultResult();
        $result['hits']['hits'][] = [
            '_index'  => 'development_mot',
            '_type'   => 'motTest',
            '_id'     => '31',
            '_score'  => null,
            '_source' => [
                'motTestNumber'       => 31,
                'status'              => 'PASSED',
                'number'              => '1234567890031',
                'primaryColour'       => 'Red',
                'hasRegistration'     => true,
                'odometerType'        => OdometerReadingResultType::OK,
                'odometerValue'       => 1234,
                'odometerUnit'        => 'mi',
                'vin'                 => '4S4BP67CX454878787',
                'registration'        => 'H66T4',
                'make'                => 'BMW',
                'model'               => 'Mini',
                'testType'            => 'Re-Test',
                'siteNumber'          => 'V1234',
                'startedDate'         => '2014-09-02 10:00:00',
                'completedDate'       => '2014-09-02 11:00:00',
                'testDate'            => '2014-09-02T11:00:00Z',
                'testerId'            => 5,
                'testerUsername'      => 'tester1',
                'reasonsForRejection' => [],
            ]
        ];
        return $result;
    }

    protected function getSearchParamsTest()
    {
        $searchParams = new MotTestSearchParam($this->mockEm);
        $searchParams
            ->setSiteNumber('V1234')
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES);

        return $searchParams;
    }

}
