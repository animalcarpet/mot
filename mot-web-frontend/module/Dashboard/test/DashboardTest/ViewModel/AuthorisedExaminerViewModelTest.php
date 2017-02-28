<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommonTest\TestUtils\XMock;
use Dashboard\Model\AuthorisedExaminer;
use PHPUnit_Framework_TestCase;

class AuthorisedExaminerViewModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorisedExaminer | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisedExaminerMock;

    /** @var DashboardGuard | \PHPUnit_Framework_MockObject_MockObject */
    private $dashboardGuardMock;

    public function setup()
    {
        $this->authorisedExaminerMock = XMock::of(AuthorisedExaminer::class);
        $this->dashboardGuardMock = XMock::of(DashboardGuard::class);
    }

    public function testShouldReturnNewAuthorisedExaminerViewModel()
    {
        $aeId = 1;

        $this->authorisedExaminerMock
            ->method('getId')
            ->willReturn($aeId);
        $url = AuthorisedExaminerUrlBuilderWeb::of($aeId);

        $vts = [];

        $aeViewModel = new AuthorisedExaminerViewModel(
            $url,
            10,
            'AE1',
            00000,
            $vts,
            10
        );

        $this->authorisedExaminerMock
            ->method('getUrl')
            ->willReturn($url);

        $this->authorisedExaminerMock
            ->method('getSiteCount')
            ->willReturn(1);

        $this->authorisedExaminerMock
            ->method('getName')
            ->willReturn('AE');

        $this->authorisedExaminerMock
            ->method('getReference')
            ->willReturn('000-000');

        $this->authorisedExaminerMock
            ->method('getSites')
            ->willReturn($vts);

        $this->authorisedExaminerMock
            ->method('getSlots')
            ->willReturn(0);

        $aeViewModelFromAuthorisedExaminer = $aeViewModel::fromAuthorisedExaminer($this->dashboardGuardMock,
                                                                                  $this->authorisedExaminerMock);

        $this->assertEquals($url, $aeViewModelFromAuthorisedExaminer->getUrl());
        $this->assertEquals(1, $aeViewModelFromAuthorisedExaminer->getVtsCount());
        $this->assertEquals("AE", $aeViewModelFromAuthorisedExaminer->getName());
        $this->assertEquals("000-000", $aeViewModelFromAuthorisedExaminer->getReference());
        $this->assertEquals($vts, $aeViewModelFromAuthorisedExaminer->getVts());
        $this->assertEquals(0, $aeViewModelFromAuthorisedExaminer->getSlots());
    }
}
