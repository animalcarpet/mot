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
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\Plugin\Url;

class PersonProfileSidebarTest extends \PHPUnit_Framework_TestCase
{
    const PERSON_ID = 1;
    const NEW_PROFILE_ENABLED = true;
    const CURRENT_URL = 'current-url';
    const TEST_LOG_URL = 'test-log-url';
    const TEST_QUALITY_URL = 'test-quality-information/%s';

    /** @var  PersonProfileSidebar */
    private $sut;
    /** @var  TesterAuthorisation | \PHPUnit_Framework_MockObject_MockObject */
    private $testerAuthorisationMock;
    /** @var  Url | \PHPUnit_Framework_MockObject_MockObject */
    private $urlPluginMock;
    /** @var  PersonProfileGuard | \PHPUnit_Framework_MockObject_MockObject */
    private $personProfileGuardMock;
    /** @var  PersonProfileRoutes | \PHPUnit_Framework_MockObject_MockObject */
    private $personProfileRoutesMock;


    public function setUp()
    {
        $this->personProfileGuardMock = XMock::of(PersonProfileGuard::class);
        $this->testerAuthorisationMock = XMock::of(TesterAuthorisation::class);
        $this->urlPluginMock = XMock::of(Url::class);
        $this->personProfileRoutesMock = XMock::of(PersonProfileRoutes::class);
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
        $relatedLinks = $sut->getSidebarItems()[1];

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
        $relatedLinks = $sut->getSidebarItems()[1];

        $this->assertSame($relatedLinks->getLinks()[0]->getUrl(), self::CURRENT_URL . '/' . $expectedLink);
    }

    public function testTestQualityLinkIsNotDisplayedWhenUserIsNotAllowed()
    {
        $this->personProfileGuardMock->expects($this->any())
            ->method('canViewTestQuality')
            ->willReturn(false);

        $sut = $this->createPersonProfileSidebar();

        $this->assertFalse(array_key_exists(1, $sut->getSidebarItems()));
    }

    public function testGetStatusBox()
    {
        $this->markTestSkipped('WIP for BL-448');
    }

    public function testGetAccountSecurityBox()
    {
        $this->markTestSkipped('WIP for BL-448');
    }

    public function testGetAccountManagementBox()
    {
        $this->markTestSkipped('WIP for BL-448');
    }

    public function testGetRelatedLinksSection()
    {
        $this->markTestSkipped('WIP for BL-448');
    }

    private function createPersonProfileSidebar()
    {
        return new PersonProfileSidebar(
            self::PERSON_ID,
            $this->personProfileGuardMock,
            $this->testerAuthorisationMock,
            self::NEW_PROFILE_ENABLED,
            self::CURRENT_URL,
            $this->personProfileRoutesMock,
            $this->urlPluginMock
        );
    }
}
