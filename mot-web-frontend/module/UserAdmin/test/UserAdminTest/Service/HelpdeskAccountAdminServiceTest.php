<?php

namespace UserAdminTest\Service;

use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase as TestCase;
use UserAdmin\Service\HelpdeskAccountAdminService;

/**
 * Test for {@link HelpdeskAccountAdminService}.
 */
class HelpdeskAccountAdminServiceTest extends TestCase
{
    /** @var HelpdeskAccountAdminService */
    private $sut;

    /** @var UserAdminMapper|\PHPUnit_Framework_MockObject_MockObject */
    private $userAdminMapperMock;

    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $authorisationMock;

    public function setUp()
    {
        $this->userAdminMapperMock = XMock::of(UserAdminMapper::class);
        $this->authorisationMock = XMock::of(MotAuthorisationServiceInterface::class);

        $this->sut = new HelpdeskAccountAdminService(
            $this->authorisationMock,
            $this->userAdminMapperMock
        );
    }

    public function testGetUserProfile()
    {
        $personId = 11;
        $expected = new PersonHelpDeskProfileDto();
        $this->userAdminMapperMock
            ->expects($this->any())
            ->method('getUserProfile')
            ->with($personId)
            ->will($this->returnValue($expected));

        $result = $this->sut->getUserProfile($personId);

        $this->assertSame($expected, $result);
    }

    public function testResetClaimAccount()
    {
        $personId = 11;
        $this->userAdminMapperMock
            ->expects($this->any())
            ->method('resetClaimAccount')
            ->with($personId)
            ->willReturn(true);

        $this->assertTrue($this->sut->resetClaimAccount($personId));
    }

    public function testPostMessage()
    {
        $this->userAdminMapperMock
            ->expects($this->any())
            ->method('postMessage')
            ->with(['test'])
            ->willReturn(true);

        $this->assertTrue($this->sut->postMessage(['test']));
    }

    public function testResetAccount()
    {
        // given
        $personId = 11;

        // then
        $this->authorisationMock
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::CREATE_MESSAGE_FOR_OTHER_USER);
        $this->userAdminMapperMock
            ->expects($this->any())
            ->method('postMessage')
            ->willReturn(true);

        // when
        $this->sut->resetAccount($personId);
    }

    /**
     * @param array $featureToggles
     *
     * @return FeatureToggles
     */
    private function getFeatureTogglesService(array $featureToggles = [])
    {
        $map = [];
        foreach ($featureToggles as $name => $value) {
            $map += [(string) $name, (bool) $value];
        }

        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();
        $featureToggles
            ->method('isEnabled')
            ->will($this->returnValueMap($map));

        return $featureToggles;
    }
}
