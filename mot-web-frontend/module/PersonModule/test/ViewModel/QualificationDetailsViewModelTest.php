<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ViewModel;

use Dvsa\Mot\Frontend\PersonModule\ViewModel\QualificationDetailsGroupViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\QualificationDetailsViewModel;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Class QualificationDetailsViewModelTest.
 */
class QualificationDetailsViewModelTest extends \PHPUnit_Framework_TestCase
{
    const RETURN_LINK = 'http://LINK';
    const RETURN_LINK_TEXT = 'Return to %s';
    const PAGE_SUBTITLE = 'subtitle';
    const GROUP_A_STATUS = 'group A status';
    const GROUP_B_STATUS = 'group B status';

    /** @var QualificationDetailsViewModel */
    private $view;
    /** @var QualificationDetailsGroupViewModel */
    private $groupAViewModelMock;
    /** @var QualificationDetailsGroupViewModel */
    private $groupBViewModelMock;
    private $isGuidanceShown = true;

    public function setup()
    {
        $this->groupAViewModelMock = Xmock::of(QualificationDetailsGroupViewModel::class);
        $this->groupBViewModelMock = Xmock::of(QualificationDetailsGroupViewModel::class);

        $this->view = new QualificationDetailsViewModel(
            self::RETURN_LINK,
            self::PAGE_SUBTITLE,
            $this->groupAViewModelMock,
            $this->groupBViewModelMock,
            $this->isGuidanceShown
        );
    }

    public function testGetPageSubtitle()
    {
        $this->assertSame(self::PAGE_SUBTITLE, $this->view->getPageSubtitle());
    }

    public function testGetReturnLinkText()
    {
        $output = sprintf(self::RETURN_LINK_TEXT, strtolower(self::PAGE_SUBTITLE));
        $this->assertSame($output, $this->view->getReturnLinkText());
    }

    public function testGetReturnLink()
    {
        $this->assertSame(self::RETURN_LINK, $this->view->getReturnLink());
    }

    public function testCanUserSeeGuidance()
    {
        $this->assertSame(true, $this->view->canUserSeeGuidance());
    }
}
