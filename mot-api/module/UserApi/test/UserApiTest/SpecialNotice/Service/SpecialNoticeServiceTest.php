<?php

namespace UserApiTest\SpecialNotice\Service;

use DateTime;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Constants\SpecialNoticeAudience as SpecialNoticeAudienceConstant;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\SpecialNotice;
use DvsaEntities\Entity\SpecialNoticeAudience;
use DvsaEntities\Entity\SpecialNoticeContent;
use DvsaEntities\Repository\SpecialNoticeRepository;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use UserApi\SpecialNotice\Service\Validator\SpecialNoticeValidator;
use Zend\Authentication\AuthenticationService;

/**
 * Class SpecialNoticeServiceTest.
 */
class SpecialNoticeServiceTest extends AbstractServiceTestCase
{
    const METHOD_SET_PARAMETER = 'setParameter';
    const METHOD_GET_RESULT    = 'getResult';
    const METHOD_CREATE_QUERY  = 'createQuery';
    const STATUS_DRAFT         = 'DRAFT';
    const STATUS_FINAL         = 'FINAL';

    /**
     * @var SpecialNoticeService
     */
    private $sut;
    private $entityManager;
    private $objectHydrator;
    private $authService;
    private $specialNoticeRepository;
    private $motIdentityProvider;
    private $specialNoticeContentRepository;

    public function setUp()
    {
        $this->specialNoticeRepository        = $this->getMockRepository(SpecialNoticeRepository::class);
        $this->specialNoticeContentRepository = $this->getMockRepository();
        $this->entityManager                  = $this->getMockEntityManager();
        $this->entityManager->expects($this->at(0))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\SpecialNotice::class)
            ->will($this->returnValue($this->specialNoticeRepository));
        $this->entityManager->expects($this->at(1))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\SpecialNoticeContent::class)
            ->will($this->returnValue($this->specialNoticeContentRepository));
        $this->objectHydrator = $this->getMockHydrator();

        $authService = $this->getMockAuthorizationService(false);
        $authService
            ->expects($this->any())
            ->method("isGranted")
            ->willReturn(false);
        $this->authService = $authService;

        $this->motIdentityProvider = XMock::of(\Zend\Authentication\AuthenticationService::class);
        $this->motIdentityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue(new MotIdentity(1, 'tester')));
        $this->sut = $this->mockSpecialNoticeServiceWithDate(new DateTime());
    }

    private function mockSpecialNoticeServiceWithDate(DateTime $dateTime = null)
    {
        if(is_null($dateTime)){
            $dateTime = new DateTime();
        }
        return new SpecialNoticeService(
            $this->entityManager,
            $this->objectHydrator,
            $this->authService,
            $this->motIdentityProvider,
            new SpecialNoticeValidator(),
            new TestDateTimeHolder($dateTime)
        );
    }

    public function testExtractShouldExtractBasicFieldsWithHydrator()
    {
        //given
        $specialNotice = $this->createTestSpecialNotice();
        $this->objectHydrator->expects($this->at(0))
            ->method('extract')
            ->with($specialNotice);
        $this->objectHydrator->expects($this->at(1))
            ->method('extract')
            ->with($specialNotice->getContent());

        //when
        $this->sut->extract($specialNotice);

        //then
        //asserts already defined on mock object
    }

    public function testExtractShouldFormatDates()
    {
        //given
        $specialNotice        = $this->createTestSpecialNotice();
        $specialNoticeContent = $specialNotice->getContent();
        $issueDate            = '2014-04-21';
        $specialNoticeContent->setIssueDate(DateUtils::toDate($issueDate));
        $expiryDate = '2014-05-04';
        $specialNoticeContent->setExpiryDate(DateUtils::toDate($expiryDate));

        //when
        $data    = $this->sut->extract($specialNotice);
        $content = $data['content'];

        //then
        $this->assertEquals($issueDate, $content['issueDate']);
        $this->assertEquals($expiryDate, $content['expiryDate']);
    }

    public function testExtractShouldFormatIssueNumber()
    {
        //given
        $specialNotice = $this->createTestSpecialNotice();
        $specialNotice->getContent()->setIssueNumber(3);
        $specialNotice->getContent()->setIssueYear(1987);

        //when
        $data = $this->sut->extract($specialNotice);

        //then
        $this->assertEquals('3-1987', $data['content']['issueNumber']);
    }

    public function testExtractWhenExpiryDatePassShouldSetIsExpiredFlagToTrue()
    {
        //given
        $specialNotice = $this->createTestSpecialNotice();
        $specialNotice->getContent()->setExpiryDate(new DateTime('2013-01-01'));

        //when
        $data = $this->sut->extract($specialNotice);

        //then
        $this->assertEquals(true, $data['isExpired']);
    }

    public function testExtractShouldProvideRolesAsString()
    {
        //given
        $specialNotice        = $this->createTestSpecialNotice();
        $specialNoticeContent = $specialNotice->getContent();
        $specialNoticeContent->addSpecialNoticeAudience(
            (new SpecialNoticeAudience())
                ->setAudienceId(3)
                ->setVehicleClassId(1)
        );

        //when
        $data = $this->sut->extract($specialNotice);

        //then
        $this->assertContains(
            SpecialNoticeAudienceConstant::TESTER_CLASS_1,
            $data['content']['targetRoles']
        );
    }

    public function testSpecialNoticeSummaryForUserWhenNoNoticesPresentShouldReturnZeroCountAndNullDeadline()
    {
        //given
        $this->mockEntityManagerForSpecialNotices([]);

        //when
        $summary = $this->sut->specialNoticeSummaryForUser('username');

        //then
        $this->assertEquals(0, $summary['overdueCount']);
        $this->assertEquals(0, $summary['unreadCount']);
        $this->assertEquals(null, $summary['acknowledgementDeadline']);
    }

    public function testSpecialNoticeSummaryForUserWhenOverdueNoticesPresentShouldCountThem()
    {
        //given
        $overdueSpecialNotice1 = $this->createTestSpecialNotice();
        $overdueSpecialNotice1->getContent()->setExpiryDate(new DateTime('2013-09-01'));
        $overdueSpecialNotice2 = $this->createTestSpecialNotice();
        $overdueSpecialNotice2->getContent()->setExpiryDate(new DateTime('2014-01-01'));
        $this->mockEntityManagerForSpecialNotices(
            [
                $overdueSpecialNotice1,
                $overdueSpecialNotice2,
            ]
        );

        //when
        $summary = $this->sut->specialNoticeSummaryForUser('username');

        //then
        $this->assertEquals(2, $summary['overdueCount']);
        $this->assertEquals(0, $summary['unreadCount']);
    }

    public function testSpecialNoticeSummaryForUserWhenNoticeIsExpiredButAcknowledgedShouldNotCountIt()
    {
        //given
        $acknowledgedExpiredSpecialNotice = $this->createTestSpecialNotice();
        $acknowledgedExpiredSpecialNotice->setIsAcknowledged(true);
        $acknowledgedExpiredSpecialNotice->getContent()->setExpiryDate(new DateTime('2013-01-01'));
        $this->mockEntityManagerForSpecialNotices([$acknowledgedExpiredSpecialNotice]);

        //when
        $summary = $this->sut->specialNoticeSummaryForUser('username');

        //then
        $this->assertEquals(0, $summary['overdueCount']);
        $this->assertEquals(0, $summary['unreadCount']);
        $this->assertEquals(null, $summary['acknowledgementDeadline']);
    }

    public function testSpecialNoticeSummaryForUserWhenUnreadNoticesShouldCalculateDeadline()
    {
        //given
        $laterExpiryDate   = '2114-01-01';
        $earlierExpiryDate = '2113-01-01';

        $unreadSpecialNotice1 = $this->createTestSpecialNotice();
        $unreadSpecialNotice1->getContent()->setExpiryDate(new DateTime($laterExpiryDate));
        $unreadSpecialNotice2 = $this->createTestSpecialNotice();
        $unreadSpecialNotice2->getContent()->setExpiryDate(new DateTime($earlierExpiryDate));
        $this->mockEntityManagerForSpecialNotices(
            [
                $unreadSpecialNotice1,
                $unreadSpecialNotice2,
            ]
        );

        //when
        $summary = $this->sut->specialNoticeSummaryForUser('username');

        //then
        $this->assertEquals(0, $summary['overdueCount']);
        $this->assertEquals(2, $summary['unreadCount']);
        $this->assertEquals($earlierExpiryDate, $summary['acknowledgementDeadline']);
    }

    public function testRemoveSpecialNoticeContentShouldRemoveContentAndAllAssociatedNotices()
    {
        //given
        $specialNoticeContentId   = 1234;
        $mockSpecialNoticeContent = new SpecialNoticeContent();
        $mockSpecialNoticeContent->setIsDeleted(false);

        $this->specialNoticeContentRepository->expects($this->once())
            ->method('find')
            ->with($specialNoticeContentId)
            ->will($this->returnValue($mockSpecialNoticeContent));

        //when
        $this->sut->removeSpecialNoticeContent($specialNoticeContentId);

        //then
        $this->assertEquals(true, $mockSpecialNoticeContent->getIsDeleted());
    }

    public function testCreateSpecialNoticeWithValidDataShouldCreateSpecialNotice()
    {
        // given
        $internalPublishDate = new DateTime("tomorrow");
        $externalPublishDate = new DateTime("tomorrow + 1day");

        $noticeData = [
            'noticeTitle'           => 'Tilte',
            'noticeText'            => 'Le Text',
            'acknowledgementPeriod' => 5,
            'internalPublishDate'   => $internalPublishDate->format('Y-m-d'),
            'externalPublishDate'   => $externalPublishDate->format('Y-m-d'),
            'targetRoles'           => [
                SpecialNoticeAudienceConstant::DVSA,
                SpecialNoticeAudienceConstant::VTS,
                SpecialNoticeAudienceConstant::TESTER_CLASS_1,
            ],
        ];
        $this->specialNoticeRepository->expects($this->never())
            ->method('getLatestIssueNumber');
        // when
        $result = $this->sut->createSpecialNotice($noticeData);

        // then
        $this->assertNull($result['issueNumber']);
        $this->assertEquals($externalPublishDate->format('Y-m-d'), $result['issueDate']);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionMessage This special notice was already published and cannot be updated
     */
    public function test_update_on_published_notice_throws_an_exception()
    {
        $id              = 1;
        $publishedNotice = (new SpecialNoticeContent())->setIsPublished(true);
        $this->setupMockForSingleCall($this->entityManager, 'find', $publishedNotice);

        $this->sut->update($id, ['key' => 'value']);
    }

    public function testCompleteSpecialNoticeStatusWithValidIdShouldUpdateStatus()
    {
        // given
        $id = 1234;

        $this->setupMockForSingleCall(
            $this->entityManager,
            'find',
            new SpecialNoticeContent()
        );

        // when
        $result = $this->sut->publish($id);

        // then
        $this->assertEquals(true, $result->isPublished());
    }

    /**
     * @dataProvider dataProviderSpecialNoticePublishDate
     */
    public function testPublishSpecialNoticeWithValidIdShouldCreateIssueNumberAndIssueDate($issueNumber, DateTime $externalPublishDate)
    {
        // given
        $id = 1234;
        $this->setupMockForSingleCall(
            $this->entityManager,
            'find',
            (new SpecialNoticeContent())->setExternalPublishDate($externalPublishDate)
        );
        $this->specialNoticeRepository->expects($this->once())
            ->method('getLatestIssueNumber')
            ->will($this->returnValue([$issueNumber]));

        $this->mockSpecialNoticeServiceWithDate($externalPublishDate);

        // when
        $result = $this->sut->publish($id);
        $this->assertEquals($result->getIssueNumber(), $issueNumber + 1);
        $this->assertEquals($result->getIssueYear(), $externalPublishDate->format('Y'));

        // then
        $this->assertTrue($result->isPublished());
    }

    public function dataProviderSpecialNoticePublishDate()
    {
        return [
            [1, (new DateTime('2015-12-31 23:59:00'))],
            [2, (new DateTime('2016-12-31 23:59:00'))],
            [2, (new DateTime('2017-12-31 23:59:00'))],
        ];
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionMessage Special Notice 1234 not found
     */
    public function testCompleteSpecialNoticeStatusWithInvalidIdShouldThrowNotFoundException()
    {
        // given
        $id = 1234;

        $this->setupMockForSingleCall($this->entityManager, 'find', null);

        // when
        $this->sut->publish($id);
    }

    /**
     * @return SpecialNotice
     */
    private function createTestSpecialNotice()
    {
        $specialNotice = new SpecialNotice();
        $specialNotice->setId(1);
        $specialNotice->setUsername('username');
        $specialNotice->setIsAcknowledged(false);

        $content = new SpecialNoticeContent();
        $content->setId(1);
        $content->setTitle('title');
        $content->setNoticeText('noticeText');
        $content->setIssueDate(new DateTime());
        $content->setIssueNumber(1);
        $content->setIssueYear(2014);
        $content->setExpiryDate(new DateTime());
        $content->addSpecialNoticeAudience(
            (new SpecialNoticeAudience())
                ->setAudienceId(3)
                ->setVehicleClassId(1)
        );
        $content->setIsPublished(false);

        $specialNotice->setContent($content);

        return $specialNotice;
    }

    private function mockEntityManagerForSpecialNotices(array $specialNotices)
    {
        $this->specialNoticeRepository
            ->expects($this->once())
            ->method('getAllCurrentSpecialNoticesForUser')
            ->will($this->returnValue($specialNotices));
    }

}
