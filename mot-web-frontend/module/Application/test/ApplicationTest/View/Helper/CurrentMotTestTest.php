<?php

namespace ApplicationTest\View\Helper;

use Application\Data\ApiCurrentMotTest;
use Application\View\Helper\CurrentMotTest;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class CurrentMotTestTest.
 */
class CurrentMotTestTest extends \PHPUnit_Framework_TestCase
{
    /** @var CurrentMotTest */
    protected $service;
    protected $activeTestUserId = 1;
    protected $noTestUserId = 2;
    protected $motTestNumber = '123456789';

    /** @var MotIdentityInterface|MockObj */
    protected $identityMock;
    protected $motIdentityProviderMock;
    /** @var ApiCurrentMotTest|MockObj */
    protected $apiCurrentMotTestMock;
    protected $serviceLocatorMock;
    protected $viewHelperServiceLocatorMock;

    /**
     * Creates the necessary mocks which the view helper consumes.
     *
     * @throws \Exception
     */
    public function setUp()
    {
        $this->identityMock = XMock::of(MotIdentityInterface::class, ['getUserId', 'getUsername']);

        $this->motIdentityProviderMock = XMock::of(MotIdentityProviderInterface::class, ['getIdentity']);
        $this->motIdentityProviderMock->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->identityMock);

        $this->apiCurrentMotTestMock = XMock::of(ApiCurrentMotTest::class, ['getCurrentMotTest']);

        $this->service = new CurrentMotTest($this->motIdentityProviderMock, $this->apiCurrentMotTestMock);
    }

    /**
     * Given a user who has an active MOT Test, the function should return the
     * mot test number.
     */
    public function testCurrentMotTestReturnsMotTestNumberWhenUserHasActiveTest()
    {
        $this->identityMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($this->activeTestUserId);

        $this->apiCurrentMotTestMock->expects($this->any())
            ->method('getCurrentMotTest')
            ->with($this->activeTestUserId)
            ->willReturn(['inProgressTestNumber' => $this->motTestNumber]);

        $response = $this->service;

        $this->assertEquals($this->motTestNumber, $response());
    }

    /**
     * Given a user with no active tests, the service should return null.
     */
    public function testCurrentMotTestReturnsNullWhenUserHasNoActiveTest()
    {
        $this->identityMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($this->noTestUserId);

        $this->apiCurrentMotTestMock->expects($this->any())
            ->method('getCurrentMotTest')
            ->with($this->noTestUserId)
            ->willReturn(['inProgressTestNumber' => null]);

        $response = $this->service;

        $this->assertEquals(null, $response());
    }
}
