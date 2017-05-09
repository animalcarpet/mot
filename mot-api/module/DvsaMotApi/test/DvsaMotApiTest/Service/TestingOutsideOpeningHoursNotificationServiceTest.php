<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Entity\SiteContactType;
use DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService;
use NotificationApi\Service\NotificationService;

/**
 * Test for TestingOutsideOpeningHoursNotificationService.
 */
class TestingOutsideOpeningHoursNotificationServiceTest extends \PHPUnit_Framework_TestCase
{
    const SITE_MANAGER_PERSON_ID = 4;
    const AEDM_PERSON_ID = 5;
    /** @var NotificationService */
    private $notificationService;

    public function setUp()
    {
        $this->notificationService = XMock::of(NotificationService::class);
    }

    public function testNotify_givenManagerAtSite_notifySiteManager()
    {
        $site = $this->site();
        $this->setSiteManager($site, self::SITE_MANAGER_PERSON_ID);
        $this->setAedm($site, self::AEDM_PERSON_ID);
        $notificationPromise = $this->notificationSent();

        $this->createService()->notify($site, $this->exampleTester(), new \DateTime(), $this->person(self::SITE_MANAGER_PERSON_ID));

        $this->assertEquals(self::SITE_MANAGER_PERSON_ID, $notificationPromise->get()['recipient']);
    }

    public function testNotify_givenNoManagerAtSite_notifyAedm()
    {
        $site = $this->setAedm($this->site(), self::AEDM_PERSON_ID);
        $notificationPromise = $this->notificationSent();

        $this->createService()->notify($site, $this->exampleTester(), new \DateTime(), $this->person(self::AEDM_PERSON_ID));

        $this->assertEquals(self::AEDM_PERSON_ID, $notificationPromise->get()['recipient']);
    }

    public function testNotify_shouldSetFieldsCorrectly()
    {
        $dateTime = DateUtils::toDateTime('2012-12-12T11:59:22Z');
        $tester = $this->exampleTester();
        $site = $this->setAedm($this->site());
        $notificationPromise = $this->notificationSent();

        $this->createService()->notify($site, $tester, $dateTime, $tester);

        $this->assertEquals(
            [
                'username' => $tester->getUsername(),
                'time' => '11:59am',
                'date' => DateTimeDisplayFormat::textDate('2012-12-12'),
                'siteNumber' => $site->getSiteNumber(),
                'address' => 'b1, b4',

            ],
            $notificationPromise->get()['fields']
        );
    }

    private function createService()
    {
        return new TestingOutsideOpeningHoursNotificationService($this->notificationService);
    }

    private function exampleTester()
    {
        return $this->person(1245)->setUsername('tester');
    }

    private function notificationSent()
    {
        $notificationCapture = ArgCapture::create();
        $this->notificationService->expects($this->once())->method('add')
            ->with($notificationCapture());

        return $notificationCapture;
    }

    private function address($lineSeed)
    {
        return (new Address())->setAddressLine1("{$lineSeed}1")
            ->setAddressLine2(null)
            ->setAddressLine3('')
            ->setAddressLine4("{$lineSeed}4");
    }

    private function site($siteNumber = '123456VC')
    {
        $site = (new Site());
        $businessContactDetail = (new ContactDetail())->setAddress($this->address('b'));
        $correspondenceContactDetail = (new ContactDetail())->setAddress($this->address('c'));

        $businessContactType = (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS);
        $correspondenceContactType = (new SiteContactType())->setCode(SiteContactTypeCode::CORRESPONDENCE);

        $site->setContact($businessContactDetail, $businessContactType);
        $site->setContact($correspondenceContactDetail, $correspondenceContactType);

        return $site->setSiteNumber($siteNumber);
    }

    private function person($id)
    {
        return (new Person())->setId($id);
    }

    private function setSiteManager(Site $site, $smId)
    {
        $role = new SiteBusinessRole();
        $role->setCode(SiteBusinessRoleCode::SITE_MANAGER);

        $status = new BusinessRoleStatus();
        $status->setCode('AC');

        $map = new SiteBusinessRoleMap();
        $map->setPerson($this->person($smId));
        $map->setSite($site);
        $map->setSiteBusinessRole($role);
        $map->setBusinessRoleStatus($status);

        $site->getPositions()->add($map);

        return $site;
    }

    private function setAedm(Site $site, $aedmId = 1)
    {
        $org = new Organisation();
        $aedmPosition = new OrganisationBusinessRoleMap();
        //$aedmPosition->accept();
        $org->getPositions()->add($aedmPosition);
        $site->setOrganisation($org);

        return $site;
    }
}
