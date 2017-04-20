<?php

namespace UserAdminTest\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\UserAdminSessionManager;
use DvsaCommonTest\Bootstrap;
use Zend\Session\Container;

/**
 * Class UserAdminSessionManagerTest
 *
 * @package UserAdminTest\Service
 */
class UserAdminSessionManagerTest extends AbstractFrontendControllerTestCase
{
    use TestCaseTrait;
    use TestCasePermissionTrait;

    const PERSON_ID = 1;

    /** @var UserAdminSessionManager */
    protected $session;
    /** @var  MotFrontendAuthorisationServiceInterface */
    private $mockAuthSrv;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $container = new Container('userAdminSession');
        $this->mockAuthSrv = XMock::of(MotFrontendAuthorisationServiceInterface::class, ['isGranted']);
        $this->session = new UserAdminSessionManager($container, $this->mockAuthSrv);
    }

    public function testUserAdminSessionManager()
    {
        $this->session->createUserAdminSession(self::PERSON_ID, []);

        $this->assertEquals(1, $this->session->getElementOfUserAdminSession('user'));
        $this->assertEquals(false, $this->session->getElementOfUserAdminSession('question1-success'));
        $this->assertEquals(3, $this->session->getElementOfUserAdminSession('question1-attempt'));
        $this->assertEquals(false, $this->session->getElementOfUserAdminSession('question2-success'));
        $this->assertEquals(3, $this->session->getElementOfUserAdminSession('question2-attempt'));
        $this->assertEquals(true, $this->session->checkElementOfUserAdminSession('user'));

        $this->session->updateUserAdminSession('question1-attempt', 2);
        $this->assertEquals(2, $this->session->getElementOfUserAdminSession('question1-attempt'));

        $this->session->deleteUserAdminSession();
        $this->assertEquals(false, $this->session->checkElementOfUserAdminSession('user'));
    }

    /**
     * @dataProvider dataProviderTestOptionalElementsOfSessionManager
     *
     * @param $key
     * @param $value
     */
    public function testOptionalElementsOfSessionManager($key, $value)
    {
        $this->session->createUserAdminSession(self::PERSON_ID, []);
        $this->assertFalse($this->session->checkElementOfUserAdminSession($key));

        $this->session->updateUserAdminSession($key, $value);
        $this->assertEquals($value, $this->session->getElementOfUserAdminSession($key));
    }

    /**
     * @dataProvider dataProviderTestIsUserAuthenticated
     */
    public function testIsUserAuthenticated(array $permission, $session, $expect)
    {
        $result = null;

        if ($permission !== null) {
            $this->mockIsGranted($this->mockAuthSrv, $permission);
        }

        if (!empty($session)) {
            $this->session->updateUserAdminSession(UserAdminSessionManager::USER_KEY, $session['userId']);
            $this->session->updateUserAdminSession(
                UserAdminSessionManager::getSuccessKey(UserAdminSessionManager::FIRST_QUESTION), $session['question1']
            );
            $this->session->updateUserAdminSession(
                UserAdminSessionManager::getSuccessKey(UserAdminSessionManager::SECOND_QUESTION), $session['question2']
            );
        }

        $result = $this->session->isUserAuthenticated(self::PERSON_ID);

        $this->assertSame($expect, $result);
    }

    public function dataProviderTestOptionalElementsOfSessionManager()
    {
        return [
            [
                'key' => UserAdminSessionManager::EMAIL_SENT,
                'value' => true,
            ],
            [
                'key' => UserAdminSessionManager::EMAIL_ADDRESS,
                'value' => 'mylocalpart@mydomain.com',
            ],
        ];
    }

    public function dataProviderTestIsUserAuthenticated()
    {
        return [
            [
                'permission' => [],
                'session' => null,
                'result' => false,
            ],
            [
                'permission' => [],
                'session' => [
                    'userId' => self::PERSON_ID,
                    'question1' => true,
                    'question2' => true,
                ],
                'result' => true,
            ],
            [
                'permission' => [],
                'session' => [
                    'userId' => 7777,
                    'question1' => true,
                    'question2' => true,
                ],
                'result' => false,
            ],
            [
                'permission' => [],
                'session' => [
                    'userId' => self::PERSON_ID,
                    'question1' => false,
                    'question2' => true,
                ],
                'result' => false,
            ],
        ];
    }
}
