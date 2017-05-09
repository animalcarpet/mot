<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;
use DvsaCommonTest\TestUtils\XMock;
use Dashboard\Model\AuthorisedExaminer;
use Zend\Mvc\Controller\Plugin\Url;

class AuthorisedExaminerViewModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var DashboardGuard | \PHPUnit_Framework_MockObject_MockObject $mockDashboardGuard */
    private $mockDashboardGuard;

    /** @var Url | \PHPUnit_Framework_MockObject_MockObject $mockUrl */
    private $mockUrl;

    public function setup()
    {
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockUrl = XMock::of(Url::class);
    }

    public function testShouldReturnNewAuthorisedExaminerViewModel()
    {
        $authorisedExaminer = $this->buildAuthorisedExaminer();

        $aeId = 1;
        $url = 'authorised-examiner/'.$aeId;
        $this->mockUrl
            ->method('fromRoute')
            ->willReturn($url);

        $aeViewModelFromAuthorisedExaminer = AuthorisedExaminerViewModel::fromAuthorisedExaminer(
            $this->mockDashboardGuard,
            $authorisedExaminer,
            $this->mockUrl
        );

        $this->assertEquals($url, $aeViewModelFromAuthorisedExaminer->getUrl());
        $this->assertEquals($authorisedExaminer->getSiteCount(), $aeViewModelFromAuthorisedExaminer->getVtsCount());
        $this->assertEquals($authorisedExaminer->getName(), $aeViewModelFromAuthorisedExaminer->getName());
        $this->assertEquals($authorisedExaminer->getReference(), $aeViewModelFromAuthorisedExaminer->getReference());
        $this->assertEquals($authorisedExaminer->getSites(), $aeViewModelFromAuthorisedExaminer->getVts());
        $this->assertEquals($authorisedExaminer->getSlots(), $aeViewModelFromAuthorisedExaminer->getSlots());
    }

    private function buildAuthorisedExaminer()
    {
        $authorisedExaminerData = [
            'id' => 1,
            'reference' => 'AE1234',
            'name' => 'AE1',
            'tradingAs' => 'AE',
            'managerId' => 5,
            'slots' => 200,
            'slotsWarnings' => 50,
            'sites' => [
                0 => [
                    'id' => 1,
                    'name' => 'V1234',
                    'siteNumber' => 'V1',
                    'positions' => [],
                ],
            ],
            'position' => '',
        ];

        return new AuthorisedExaminer($authorisedExaminerData);
    }
}
