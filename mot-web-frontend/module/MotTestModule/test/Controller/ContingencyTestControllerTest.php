<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use Application\Service\LoggedInUserManager;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use Dvsa\Mot\Frontend\MotTestModule\Controller\ContingencyTestController;
use Dvsa\Mot\Frontend\MotTestModule\Validation\ContingencyTestValidator;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\Validation\ValidationResult;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;

/**
 * ContingencyTestController Test.
 */
class ContingencyTestControllerTest extends AbstractFrontendControllerTestCase
{
    private $loggedInUserManagerMock;

    /**
     * @var ContingencyTestValidator
     */
    private $contingencyTestValidator;

    protected function setUp()
    {
        Bootstrap::setupServiceManager();
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->contingencyTestValidator = $this->createContingencyTestValidator(true);

        $this->setController(new ContingencyTestController($this->contingencyTestValidator));
        $this->getController()->setServiceLocator($this->serviceManager);

        $this->loggedInUserManagerMock = XMock::of(
            LoggedInUserManager::class,
            ['getAllVts', 'getTesterData', 'changeCurrentLocation']
        );

        $this->serviceManager->setService('LoggedInUserManager', $this->loggedInUserManagerMock);
        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param array      $params            Post parameters
     * @param array      $permissions       Permissions
     * @param bool       $differentVts      If its tested at a different vts
     * @param string     $expectedUrl       Expect redirect if failure
     * @param \Exception $expectedException Expect exception
     *
     * @dataProvider dataProviderContingencyControllerTestCanAccessHasRight
     */
    public function testContingencyControllerCanAccessHasRight($params = null, $permissions = [], $differentVts = true,
                                                               $expectedUrl = null, $expectedException = null)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService($permissions);

        if ($params !== null) {
            $this->setPostData($params, $expectedException, $differentVts);
        }

        $this->getResponseForAction('index');

        if ($expectedUrl) {
            $this->assertRedirectLocation2($expectedUrl);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }
    }

    /**
     * @return array
     */
    public function dataProviderContingencyControllerTestCanAccessHasRight()
    {
        return [
            [null, [PermissionInSystem::EMERGENCY_TEST_READ]],
            [null, [], false, '/'],
            [[
                'site_id' => '1',
                'radio-test-type-group' => 'normal',
                'contingency_code' => '12345A',
                'testerNumber' => '1',
                'performed_at_year' => '2014',
                'performed_at_month' => '01',
                'performed_at_day' => '01',
                'performed_at_hour' => '8',
                'performed_at_minute' => '30',
                'performed_at_am_pm' => 'am',
                'reason' => 'SO',
                'other_reason_text' => '',
            ], [PermissionInSystem::EMERGENCY_TEST_READ], false, '/vehicle-search?contingency=1'],
            [[
                'site_id' => '1',
                'radio-test-type-group' => 'retest',
                'contingency_code' => '12345A',
                'testerNumber' => '1',
                'performed_at_year' => '2014',
                'performed_at_month' => '01',
                'performed_at_day' => '01',
                'performed_at_hour' => '8',
                'performed_at_minute' => '30',
                'performed_at_am_pm' => 'am',
                'reason' => 'SO',
                'other_reason_text' => '',
            ], [PermissionInSystem::EMERGENCY_TEST_READ], false, '/vehicle-search?contingency=1'],
            [[
                'site_id' => '1',
                'radio-test-type-group' => 'normal',
                'contingency_code' => '12345A',
                'testerNumber' => '1',
                'performed_at_year' => '2014',
                'performed_at_month' => '01',
                'performed_at_day' => '01',
                'performed_at_hour' => '8',
                'performed_at_minute' => '30',
                'performed_at_am_pm' => 'am',
                'reason' => 'SO',
                'other_reason_text' => '',
            ], [PermissionInSystem::EMERGENCY_TEST_READ], true, '/vehicle-search?contingency=1'],
            [[
                'site_id' => '1',
                'radio-test-type-group' => 'normal',
                'contingency_code' => '12345A',
                'testerNumber' => '1',
                'performed_at_year' => '2014',
                'performed_at_month' => '01',
                'performed_at_day' => '01',
                'performed_at_hour' => '8',
                'performed_at_minute' => '30',
                'performed_at_am_pm' => 'am',
                'reason' => 'SO',
                'other_reason_text' => '',
            ], [PermissionInSystem::EMERGENCY_TEST_READ], false, null, new RestApplicationException('', 'post', [], 404)],
            [[
                'site_id' => '1',
                'radio-test-type-group' => 'normal',
                'contingency_code' => '12345A',
                'testerNumber' => '1',
                'performed_at_year' => '2014',
                'performed_at_month' => '01',
                'performed_at_day' => '01',
                'performed_at_hour' => '8',
                'performed_at_minute' => '30',
                'performed_at_am_pm' => 'am',
                'reason' => 'SO',
                'other_reason_text' => '',
            ], [PermissionInSystem::EMERGENCY_TEST_READ], false, null, new GeneralRestException('', 'post', [], 404)],
        ];
    }

    /**
     * @param $params
     * @param $expectedException
     * @param $differentVts
     */
    private function setPostData($params, $expectedException, $differentVts)
    {
        $this->setPostAndPostParams($params);
        if ($expectedException !== null) {
            $this->getRestClientMockThrowingSpecificException('post', $expectedException);
        } else {
            $this->getRestClientMock('post', ['data' => ['emergencyLogId' => 1]]);
            if ($differentVts === true) {
                $identity = $this->getCurrentIdentity();
                $identity->setCurrentVts(new VehicleTestingStation(['id' => 2]));
                $this->loggedInUserManagerMock->expects($this->once())
                    ->method('getTesterData')
                    ->willReturn(null);
                $this->loggedInUserManagerMock->expects($this->once())
                    ->method('changeCurrentLocation')
                    ->willReturn(null);
            } else {
                $identity = $this->getCurrentIdentity();
                $identity->setCurrentVts(new VehicleTestingStation(['id' => 1]));
            }
        }
    }

    /**
     * @param bool $shouldValidate
     *
     * @return ContingencyTestValidator
     */
    private function createContingencyTestValidator($shouldValidate)
    {
        $contingencyTestValidator = $this
            ->getMockBuilder(ContingencyTestValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validationResult = new ValidationResult($shouldValidate);

        $contingencyTestValidator
            ->method('validate')
            ->willReturn($validationResult);

        return $contingencyTestValidator;
    }
}
