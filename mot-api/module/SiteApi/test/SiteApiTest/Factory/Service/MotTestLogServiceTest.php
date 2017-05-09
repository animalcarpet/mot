<?php

namespace SiteApiTest\Service\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\Site\MotTestLogSummaryDto;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Service\Mapper\MotTestLogSummaryMapper;
use SiteApi\Service\MotTestLogService;

class MotTestLogServiceTest extends AbstractServiceTestCase
{
    const SITE_ID = 1;
    const YEAR = '1024';
    const MONTH = '256';
    const WEEK = '12';
    const DAY = '2';

    /** @var MotTestLogService */
    private $motTestLogService;
    /** @var AuthorisationServiceInterface|MockObj */
    private $mockAuthSrv;
    /** @var EntityManager|MockObj */
    private $mockEm;
    /** @var MotTestRepository|MockObj */
    private $mockMotRepo;

    public function setUp()
    {
        $this->mockAuthSrv = $this->getMockAuthorizationService();
        $this->mockEm = XMock::of(EntityManager::class, ['getRepository']);
        $this->mockMotRepo = XMock::of(MotTestRepository::class, ['getCountOfSiteMotTestsSummary']);

        $this->motTestLogService = new MotTestLogService(
            $this->mockAuthSrv,
            $this->mockMotRepo,
            new MotTestLogSummaryMapper(new Hydrator())
        );
    }

    public function testMotTestLogService()
    {
        // This test doesn't assert anything. It's to check if code is not broken.
        $this->mockEm->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->mockMotRepo);

        $this->mockMotRepo->expects($this->any())
            ->method('getCountOfSiteMotTestsSummary')
            ->with(self::SITE_ID)
            ->willReturn($this->getMotTestBySite());

        $result = $this->motTestLogService->getMotTestLogSummaryForSite(self::SITE_ID);

        $this->assertInstanceOf(MotTestLogSummaryDto::class, $result);
        $this->assertEquals(self::YEAR, $result->getYear());
        $this->assertEquals(self::MONTH, $result->getMonth());
        $this->assertEquals(self::WEEK, $result->getWeek());
        $this->assertEquals(self::DAY, $result->getToday());
    }

    protected function getMotTestBySite()
    {
        return [
            'year' => self::YEAR,
            'month' => self::MONTH,
            'week' => self::WEEK,
            'today' => self::DAY,
        ];
    }
}
