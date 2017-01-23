<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\View;

use Core\ViewModel\Sidebar\GeneralSidebarLinkList;
use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileSidebar;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\Plugin\Url;


class PersonProfileSidebarTest extends \PHPUnit_Framework_TestCase
{
    const PERSON_ID = 1;
    const CURRENT_URL = '/profile/1';
    const TEST_LOG_URL = 'test-log-url';
    const TEST_QUALITY_URL = 'test-quality-information/%s';

    /** @var  PersonProfileSidebar */
    private $sut;

    /** @var  TesterAuthorisation | \PHPUnit_Framework_MockObject_MockObject */
    private $testerAuthorisationMock;

    /** @var bool */
    private $isTwoFactorAuthEnabled;

    /** @var bool */
    private $canOrderSecurityCard;

    /** @var  PersonProfileRoutes | \PHPUnit_Framework_MockObject_MockObject */
    private $personProfileRoutesMock;

    /** @var  Url | \PHPUnit_Framework_MockObject_MockObject */
    private $urlPluginMock;

    /** @var  PersonProfileGuard | \PHPUnit_Framework_MockObject_MockObject */
    private $personProfileGuardMock;

    /** @var TesterAuthorisation */
    private $testerAuthorisation;

    /** @var bool */
    private $hasSecurityCardOrders;

    /** @var bool */
    private $hasDeactivated2FaCard;

    /** @var bool */
    private $isAuthenticatedWithLostAndForgotten;

    public function setUp()
    {
        $this->personProfileGuardMock = XMock::of(PersonProfileGuard::class);
        $this->testerAuthorisationMock = XMock::of(TesterAuthorisation::class);
        $this->urlPluginMock = XMock::of(Url::class);
        $this->personProfileRoutesMock = XMock::of(PersonProfileRoutes::class);
        $this->hasSecurityCardOrders = false;
        $this->hasDeactivated2FaCard = false;
        $this->isAuthenticatedWithLostAndForgotten = false;
    }

    public function testTestLogsLinkIsDisplayed()
    {
        $this->personProfileGuardMock->expects($this->any())
            ->method('canViewTestLogs')
            ->willReturn(true);

        $this->urlPluginMock->expects($this->any())
            ->method('fromRoute')
            ->willReturn(self::TEST_LOG_URL);

        $sut = $this->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $relatedLinks */
        $relatedLinks = $sut->getSidebarItems()[2];

        $this->assertSame($relatedLinks->getLinks()[0]->getUrl(), self::TEST_LOG_URL);
    }

    public function testTestQualityLinkIsDisplayed()
    {
        $expectedLink =
            sprintf(
            self::TEST_QUALITY_URL,
                DateUtils::subtractCalendarMonths(
                    DateUtils::toUserTz(DateUtils::firstOfThisMonth()), 1)
                    ->format("m/Y")
            );

        $this->personProfileGuardMock->expects($this->any())
            ->method('canViewTestQuality')
            ->willReturn(true);

        $this->urlPluginMock->expects($this->any())
            ->method('fromRoute')
            ->willReturn($expectedLink);

        $sut = $this->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $relatedLinks */
        $relatedLinks = $sut->getSidebarItems()[2];

        $this->assertSame($relatedLinks->getLinks()[0]->getUrl(), self::CURRENT_URL . '/' . $expectedLink);
    }

    public function testTestQualityLinkIsNotDisplayedWhenUserIsNotAllowed()
    {
        $this->personProfileGuardMock->expects($this->any())
            ->method('canViewTestQuality')
            ->willReturn(false);

        $sut = $this->createPersonProfileSidebar();

        $this->assertFalse(array_key_exists(2, $sut->getSidebarItems()));
    }

    public function testSideBarDoesNotContainTwoFactorLinks()
    {
        $sidebar = $this
            ->withIsExpectedToRegisterForTwoFactorAuth(false)
            ->withTwoFactorAuthEnabled(true)
            ->withFullyAuthorisedTester()
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertCount(3, $sidebarLinks->getLinks());

        $this->assertContains('Change your password', $linkTexts);
        $this->assertContains('Reset your PIN', $linkTexts);
        $this->assertContains('Change your security questions', $linkTexts);
    }

    public function testSideBarDoesNotContainOrderLink_whenGroupADemoTestIsNeeded()
    {
        $sidebar = $this
            ->withTwoFactorAuthEnabled(true)
            ->withIsExpectedToRegisterForTwoFactorAuth(true)
            ->withUserThatCanOrderASecurityCard(false)
            ->withGroupADemoTestNeeded()
            ->createPersonProfileSidebar();

        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertCount(4, $sidebarLinks->getLinks());

        $this->assertContains('Change your password', $linkTexts);
        $this->assertContains('Reset your PIN', $linkTexts);
        $this->assertContains('Activate your security card', $linkTexts);
        $this->assertContains('Change your security questions', $linkTexts);
    }

    public function testSideBarDoesNotContainOrderLink_whenGroupBDemoTestIsNeeded()
    {
        $sidebar = $this
            ->withTwoFactorAuthEnabled(true)
            ->withIsExpectedToRegisterForTwoFactorAuth(true)
            ->withUserThatCanOrderASecurityCard(false)
            ->withGroupBDemoTestNeeded()
            ->createPersonProfileSidebar();

        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertCount(4, $sidebarLinks->getLinks());

        $this->assertContains('Change your password', $linkTexts);
        $this->assertContains('Reset your PIN', $linkTexts);
        $this->assertContains('Activate your security card', $linkTexts);
        $this->assertContains('Change your security questions', $linkTexts);
    }

    public function testSideBarDoesContainTwoFactorLinks()
    {
        $sidebar = $this
            ->withIsExpectedToRegisterForTwoFactorAuth(true)
            ->withTwoFactorAuthEnabled(true)
            ->withFullyAuthorisedTester()
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertCount(4, $sidebarLinks->getLinks());

        $this->assertContains('Change your password', $linkTexts);
        $this->assertContains('Reset your PIN', $linkTexts);
        $this->assertContains('Activate your security card', $linkTexts);
        $this->assertContains('Change your security questions', $linkTexts);
    }

    /**
     * @param bool $canOrderSecurityCard
     * @param bool $hasSecurityCardOrders
     * @param bool $hasDeactivated2FaCard
     * @param bool $isAuthenticatedWithLostAndForgotten
     * @param bool $expectActivateLink
     * @param bool $expectCardOrderLink
     *
     * @dataProvider truthMatrixActivateCardLinkProvider
     */
    public function testSideBarHidesShowsActivateAndOrderLinkCorrently(
                $canOrderSecurityCard, $isExpectedToRegisterForTwoFactorAuth, $expectActivateLink, $expectCardOrderLink)
    {
        $sidebar = $this
            ->withUserThatCanOrderASecurityCard($canOrderSecurityCard)
            ->withIsExpectedToRegisterForTwoFactorAuth($isExpectedToRegisterForTwoFactorAuth)
            ->withTwoFactorAuthEnabled(true)
            ->withFullyAuthorisedTester()
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        if ($expectActivateLink) {
            $this->assertContains('Activate your security card', $linkTexts);
        } else {
            $this->assertNotContains('Activate your security card', $linkTexts);
        }

        if ($expectCardOrderLink) {
            $this->assertContains('Order a security card', $linkTexts);
        } else {
            $this->assertNotContains('Order a security card', $linkTexts);
        }
    }

    /**
     * @return array
     */
    public function truthMatrixActivateCardLinkProvider()
    {
        return [
            [false, true, true, false],
            [false, false, false, false],
            [false, false, false, false],
            [false, false, false, false],
            [true, true, false, true],
        ];
    }

    public function testSideBarDoesNotContainTwoFactorLinksIfTwoFactAuthDisabled()
    {
        $sidebar = $this
            ->withIsExpectedToRegisterForTwoFactorAuth(true)
            ->withTwoFactorAuthEnabled(false)
            ->withFullyAuthorisedTester()
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertCount(3, $sidebarLinks->getLinks());

        $this->assertContains('Change your password', $linkTexts);
        $this->assertContains('Reset your PIN', $linkTexts);
    }

    public function testSideBarDoesNotContainOrderSecurityCardIfNotAuthenticatedWith2FA()
    {
        $sidebar = $this
            ->withIsExpectedToRegisterForTwoFactorAuth(false)
            ->withTwoFactorAuthEnabled(true)
            ->withFullyAuthorisedTester()
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertCount(3, $sidebarLinks->getLinks());

        $this->assertNotContains('Order a security card', $linkTexts);
    }

    public function testSideBarDoesContainOrderSecurityCardIfUserCanOrderACard()
    {
        $sidebar = $this
            ->withIsExpectedToRegisterForTwoFactorAuth(false)
            ->withTwoFactorAuthEnabled(true)
            ->withFullyAuthorisedTester()
            ->withUserThatCanOrderASecurityCard(true)
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertContains('Order a security card', $linkTexts);
    }

    public function testFullyAuthorisedTestersShouldNotSeeOrderSecurityCardLinkWhenUserCannotOrderACard()
    {
        $sidebar = $this
            ->withIsExpectedToRegisterForTwoFactorAuth(false)
            ->withTwoFactorAuthEnabled(true)
            ->withFullyAuthorisedTester()
            ->withUserThatCanOrderASecurityCard(false)
            ->createPersonProfileSidebar();

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[1];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertNotContains('Order a security card', $linkTexts);
    }

    public function testAccountManagementReclaimAccount_whenCanResetAccount_shouldContainBothLinks()
    {
        $sidebar = $this
            ->withUserThatCanViewAccountManagement(true)
            ->withUserThatCanResetAccount(true)
            ->createPersonProfileSidebar(true);

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[2];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertSame(2, count($linkTexts));
        $this->assertSame('Reset account security by email', $linkTexts[0]);
        $this->assertSame('Reset account by post', $linkTexts[1]);
    }

    public function testAccountManagementReclaimAccount_whenCannotResetByEmail_shouldOnlyContainByPostLink()
    {
        $sidebar = $this
            ->withUserThatCanViewAccountManagement(true)
            ->withUserThatCanResetAccount(true)
            ->createPersonProfileSidebar(false);

        /** @var GeneralSidebarLinkList $sidebarLinks */
        $sidebarLinks = $sidebar->getSidebarItems()[2];
        $linkTexts = $this->getTextFromSidebarLinks($sidebarLinks);

        $this->assertSame(1, count($linkTexts));
        $this->assertSame('Reset account by post', $linkTexts[0]);
    }

    private function getTextFromSidebarLinks(GeneralSidebarLinkList $sidebarLinks)
    {
        $linkTexts = [];
        foreach ($sidebarLinks->getLinks() as $link) {
            $linkTexts[] = $link->getText();
        }

        return $linkTexts;
    }

    private function withUserThatCanResetAccount($canResetAccount)
    {
        $this->personProfileGuardMock
            ->expects($this->any())
            ->method('canResetAccount')
            ->willReturn($canResetAccount);

        return $this;
    }

    private function withUserThatCanViewAccountManagement($canViewAccountManagement)
    {
        $this->personProfileGuardMock
            ->expects($this->any())
            ->method('canViewAccountManagement')
            ->willReturn($canViewAccountManagement);

        return $this;
    }

    /**
     * @param bool $isExpected
     * @return $this
     */
    private function withIsExpectedToRegisterForTwoFactorAuth($isExpected)
    {
        $this->personProfileGuardMock
            ->expects($this->any())
            ->method('isExpectedToRegisterForTwoFactorAuth')
            ->willReturn($isExpected);

        return $this;
    }

    /**
     * @param bool $isTwoFactorAuthEnabled
     * @return $this
     */
    private function withTwoFactorAuthEnabled($isTwoFactorAuthEnabled)
    {
        $this->isTwoFactorAuthEnabled = $isTwoFactorAuthEnabled;

        return $this;
    }

    private function withUserThatCanOrderASecurityCard($canOrderSecurityCard)
    {
        $this->canOrderSecurityCard = $canOrderSecurityCard;
        return $this;
    }

    private function withFullyAuthorisedTester()
    {
        $status = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, "name");
        $this->testerAuthorisation = new TesterAuthorisation($status, $status);
        return $this;
    }

    private function withGroupADemoTestNeeded()
    {
        $demoTestStatus = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, "name");
        $status = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, "name");
        $this->testerAuthorisation = new TesterAuthorisation($demoTestStatus, $status);
        return $this;
    }

    private function withGroupBDemoTestNeeded()
    {
        $demoTestStatus = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, "name");
        $status = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, "name");
        $this->testerAuthorisation = new TesterAuthorisation($status, $demoTestStatus);
        return $this;
    }

    /**
     * @param $canDisplayResetAccountLink
     * @return PersonProfileSidebar
     */
    private function createPersonProfileSidebar($canDisplayResetAccountLink = true)
    {
        $hideResetPin = false;

        $this->personProfileGuardMock
            ->expects($this->any())
            ->method('canViewAccountSecurity')
            ->willReturn(true);

        return new PersonProfileSidebar(
            self::PERSON_ID,
            $this->personProfileGuardMock,
            $this->testerAuthorisationMock,
            self::CURRENT_URL,
            $this->personProfileRoutesMock,
            $this->urlPluginMock,
            $hideResetPin,
            $this->isTwoFactorAuthEnabled,
            $this->canOrderSecurityCard,
            $this->hasSecurityCardOrders,
            $this->hasDeactivated2FaCard,
            $this->isAuthenticatedWithLostAndForgotten,
            $canDisplayResetAccountLink
        );
    }
}
