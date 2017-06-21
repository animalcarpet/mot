<?php

namespace DvsaEntitiesTest\Cache\Repository;

use Doctrine\Common\Cache\Cache;
use DvsaCommon\Model\PersonAuthorization;
use DvsaEntities\Cache\Repository\CachedRbacRepository;
use DvsaEntities\Repository\RbacRepository;

class CachedRbacRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CachedRbacRepository
     */
    private $cachedRbacRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rbacRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var array
     */
    private $options = ['ttl' => ['authorization_details' => 60]];

    public function setUp()
    {
        $this->rbacRepository = $this->getMockBuilder(RbacRepository::class)->disableOriginalConstructor()->getMock();
        $this->cache = $this->getMockBuilder(Cache::class)->disableOriginalConstructor()->getMock();
        $this->cachedRbacRepository = new CachedRbacRepository(
            $this->rbacRepository,
            $this->cache,
            $this->options
        );
    }

    public function testItIsAnRbacRepository()
    {
        $this->assertInstanceOf(RbacRepository::class, $this->cachedRbacRepository);
    }

    public function testItProxiesPersonIdHasRoleCall()
    {
        $this->rbacRepository->expects($this->any())
            ->method('personIdHasRole')
            ->with(105, 'tester')
            ->willReturn(true);

        $this->assertTrue($this->cachedRbacRepository->personIdHasRole(105, 'tester'));
    }

    public function testAuthorizationDetailsCall()
    {
        $personAuthorization = $this->getPersonAuthorization();

        $this->rbacRepository->expects($this->any())
            ->method('authorizationDetails')
            ->with(105)
            ->willReturn($personAuthorization);

        $this->assertSame($personAuthorization, $this->cachedRbacRepository->authorizationDetails(105));
    }

    public function testItReturnsCachedPersonAuthorization()
    {
        $personAuthorization = $this->getPersonAuthorization();

        $this->rbacRepository->expects($this->never())
            ->method('authorizationDetails');

        $this->cache->method('fetch')
            ->with('person_authorization_105')
            ->willReturn($personAuthorization);

        $this->assertSame($personAuthorization, $this->cachedRbacRepository->authorizationDetails(105));
    }

    public function testItSavesPersonAuthorizationInCache()
    {
        $personAuthorization = $this->getPersonAuthorization();

        $this->rbacRepository->expects($this->any())
            ->method('authorizationDetails')
            ->with(105)
            ->willReturn($personAuthorization);

        $this->cache->expects($this->once())
            ->method('save')
            ->with('person_authorization_105', $personAuthorization, $this->options['ttl']['authorization_details']);

        $this->cachedRbacRepository->authorizationDetails(105);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPersonAuthorization()
    {
        return $this->getMockBuilder(PersonAuthorization::class)->disableOriginalConstructor()->getMock();
    }
}
