<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\AuthAdapter;

use Dvsa\Mot\Frontend\AuthenticationModule\AuthAdapter\Rest;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Authentication\Result;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

/**
 * Class RestTest.
 */
class RestTest extends PHPUnit_Framework_TestCase
{
    const TEST_USERNAME = 'username_for_testing';
    const TEST_PASSWORD = 'p4ss_4_t3sting';
    const TEST_ACCESS_TOKEN = '52147519c32447.50887395';

    private function WebAuthenticationCookieServiceMock()
    {
        $mock = XMock::of(WebAuthenticationCookieService::class);
        $mock->expects($this->any())->method('getToken')->willReturn(self::TEST_ACCESS_TOKEN);

        return $mock;
    }

    public function testRestAdapterPostingAndGettingResponse()
    {
        $authAdapter = new Rest($this->getJsonClientMock(), $this->WebAuthenticationCookieServiceMock());
        $authResult = $authAdapter->authenticate();
        $identity = $authResult->getIdentity();

        $this->assertEquals(self::TEST_USERNAME, $identity->getUsername());
        $this->assertEquals(self::TEST_ACCESS_TOKEN, $identity->getAccessToken());
    }

    public function testRestAdapterPostingAndNotGettingResponse()
    {
        $authAdapter = new Rest($this->getJsonClientMock(false), $this->WebAuthenticationCookieServiceMock());
        $authResult = $authAdapter->authenticate();
        $code = $authResult->getCode();

        $this->assertEquals(Result::FAILURE_UNCATEGORIZED, $code);
    }

    protected function getJsonClientMock($isCommunicationWorking = true)
    {
        $result = ['data' => [
                                'code' => Result::SUCCESS,
                                'identity' => self::TEST_USERNAME,
                                'accessToken' => self::TEST_ACCESS_TOKEN,
                                'messages' => [],
                                'user' => [
                                    'userId' => 1,
                                    'username' => self::TEST_USERNAME,
                                    'displayName' => 'Test Name',
                                    'role' => 'Test Role',
                                    'passwordExpiryDate' => '2000-12-12T12:12:12Z',
                                    'isAccountClaimRequired' => false,
                                    'isPasswordChangeRequired' => false,
                                    'isSecondFactorRequired' => false,
                                    'rbacData' => [
                                        'normal' => [
                                                'roles' => [
                                                    'NORMAL-ROLE-1',
                                                ],
                                                'permissions' => [
                                                    'NORMAL-ROLE-1-PERMISSION-1',
                                                ],
                                            ],
                                        'sites' => [
                                            '10' => [
                                                'roles' => ['SITE-ROLE-A'],
                                                'permissions' => ['SITE-ROLE-A-PERMISSION-1'],
                                            ],
                                        ],
                                        'organisations' => [
                                        ],
                                        'siteOrganisationMap' => [
                                            '10' => '1',
                                            '20' => '3',
                                            '30' => '2',
                                        ],
                                    ],
                                ],
                            ],
        ];
        $restClientMock = \DvsaCommonTest\TestUtils\XMock::of(HttpRestJsonClient::class);
        $restClientMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($isCommunicationWorking ? $result : null));

        return $restClientMock;
    }
}
