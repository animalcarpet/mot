<?php

namespace DvsaCommonTest\Auth\Assertion;

use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;

class ViewTesterTestQualityAssertionTest extends \PHPUnit_Framework_TestCase
{
    const LOGGED_USER_ID = 1;
    const PERSON_ID = 2;

    /** @var AuthorisationServiceMock */
    private $authService;
    private $identityProvider;

    public function setUp()
    {
        $this->authService = new AuthorisationServiceMock();
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);

        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method("getUserId")
            ->willReturn(self::LOGGED_USER_ID);

        $this
            ->identityProvider
            ->expects($this->any())
            ->method("getIdentity")
            ->willReturn($identity);
    }

    /** @return ViewTesterTestQualityAssertion */
    private function createAssertion()
    {
        return new ViewTesterTestQualityAssertion(
            $this->authService,
            $this->identityProvider
        );
    }

    /**
     * @dataProvider getValidTesterGroupAuthorisationStatus
     * @param TesterGroupAuthorisationStatus $groupAStatus
     * @param TesterGroupAuthorisationStatus $groupBStatus
     */
    public function testIsGrantedReturnsTrueForValidDataWhenPersonViewTheirOwnStats(TesterGroupAuthorisationStatus $groupAStatus = null, TesterGroupAuthorisationStatus $groupBStatus = null)
    {
        $isGranted = $this->createAssertion()->isGranted(
            self::LOGGED_USER_ID,
            new TesterAuthorisation($groupAStatus, $groupBStatus)
        );

        $this->assertTrue($isGranted);
    }

    /**
     * @dataProvider getInvalidTesterGroupAuthorisationStatus
     * @param TesterGroupAuthorisationStatus $groupAStatus
     * @param TesterGroupAuthorisationStatus $groupBStatus
     */
    public function testIsGrantedReturnsFalseForInvalidDataWhenPersonViewTheirOwnStats(TesterGroupAuthorisationStatus $groupAStatus = null, TesterGroupAuthorisationStatus $groupBStatus = null)
    {
        $isGranted = $this->createAssertion()->isGranted(
            self::LOGGED_USER_ID,
            new TesterAuthorisation($groupAStatus, $groupBStatus)
        );

        $this->assertFalse($isGranted);
    }

    /**
     * @dataProvider getInvalidTesterGroupAuthorisationStatus
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     * @param TesterGroupAuthorisationStatus $groupAStatus
     * @param TesterGroupAuthorisationStatus $groupBStatus
     * @throws \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAssertGrantedThrowsExceptionForInvalidDataWhenPersonViewTheirOwnStats(TesterGroupAuthorisationStatus $groupAStatus = null, TesterGroupAuthorisationStatus $groupBStatus = null)
    {
        $this->createAssertion()->assertGranted(
            self::LOGGED_USER_ID,
            new TesterAuthorisation($groupAStatus, $groupBStatus)
        );
    }

    public function testIsGrantedReturnsTrueForCorrectPermissionWhenPersonViewOtherPersonStats()
    {
        $this->authService->granted(PermissionInSystem::TESTER_VIEW_TEST_QUALITY);

        $isGranted = $this->createAssertion()->isGranted(
            self::PERSON_ID,
            new TesterAuthorisation(new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, "status"))
        );

        $this->assertTrue($isGranted);
    }

    public function testIsGrantedReturnsFalseForIncorrectPermissionWhenPersonViewOtherPersonStats()
    {
        $this->authService->clearAll();

        $isGranted = $this->createAssertion()->isGranted(
            self::PERSON_ID,
            new TesterAuthorisation()
        );

        $this->assertFalse($isGranted);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAssertGrantedThrowsExceptionForIncorrectPermissionWhenPersonViewOtherPersonStats()
    {
        $this->authService->clearAll();

        $this->createAssertion()->assertGranted(
            self::PERSON_ID,
            new TesterAuthorisation()
        );
    }

    public function getValidTesterGroupAuthorisationStatus()
    {
        $allowedStatuses = [
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            AuthorisationForTestingMotStatusCode::SUSPENDED,
        ];

        $data = [];
        foreach ($allowedStatuses as $status) {
            $data[] = [
                new TesterGroupAuthorisationStatus($status, "status"),
                null
            ];

            $data[] = [
                new TesterGroupAuthorisationStatus($status, "status"),
                new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED, "status"),
            ];

            $data[] = [
                new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED, "status"),
                new TesterGroupAuthorisationStatus($status, "status"),
            ];

            $data[] = [
                null,
                new TesterGroupAuthorisationStatus($status, "status"),
            ];

            $data[] = [
                new TesterGroupAuthorisationStatus($status, "status"),
                new TesterGroupAuthorisationStatus($status, "status"),
            ];
        }

        return $data;
    }

    public function getInvalidTesterGroupAuthorisationStatus()
    {
        return [
            [
                null,
                null
            ],
            [
                null,
                new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED, "status"),
            ],
            [
                new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED, "status"),
                null
            ],
            [
                new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED, "status"),
                new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED, "status"),
            ]
        ];
    }
}
