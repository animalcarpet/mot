<?php
namespace DvsaAuthenticationTest\AuthAdapter;

use DvsaAuthentication\AuthAdapter\Rest;
use DvsaAuthentication\Service\WebAccessTokenService;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Authentication\Result;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

/**
 * Class RestTest
 */
class RestTest extends PHPUnit_Framework_TestCase
{
    const TEST_USERNAME = 'username_for_testing';
    const TEST_PASSWORD = 'p4ss_4_t3sting';
    const TEST_ACCESS_TOKEN = '52147519c32447.50887395';


    private function webAccessTokenServiceMock()
    {
        $mock = XMock::of(WebAccessTokenService::class);
        $mock->expects($this->any())->method('getToken')->willReturn(self::TEST_ACCESS_TOKEN);
        return $mock;
    }

    public function testRestAdapterPostingAndGettingResponse()
    {
        $authAdapter = new Rest($this->getJsonClientMock(), $this->webAccessTokenServiceMock());
        $authResult = $authAdapter->authenticate();
        $identity = $authResult->getIdentity();

        $this->assertEquals(self::TEST_USERNAME, $identity->getUsername());
        $this->assertEquals(self::TEST_ACCESS_TOKEN, $identity->getAccessToken());
    }

    public function testRestAdapterPostingAndNotGettingResponse()
    {
        $authAdapter = new Rest($this->getJsonClientMock(false), $this->webAccessTokenServiceMock());
        $authResult = $authAdapter->authenticate();
        $code = $authResult->getCode();

        $this->assertEquals(Result::FAILURE_UNCATEGORIZED, $code);
    }

    protected function getJsonClientMock($isCommunicationWorking = true)
    {
        $result = ['data' =>
                            [
                                'code'        => Result::SUCCESS,
                                'identity'    => self::TEST_USERNAME,
                                'accessToken' => self::TEST_ACCESS_TOKEN,
                                'messages'    => [],
                                'user'        => [
                                    'userId'      => 1,
                                    'username'    => self::TEST_USERNAME,
                                    'displayName' => 'Test Name',
                                    'role'        => 'Test Role',
                                    'roles'       => [SiteBusinessRoleCode::TESTER],
                                    'isAccountClaimRequired' => false,
                                    'isPasswordChangeRequired' => false,
                                    'rbacData' =>   [
                                        "normal"              =>
                                            [
                                                "roles"       => [
                                                    "NORMAL-ROLE-1",
                                                ],
                                                "permissions" => [
                                                    "NORMAL-ROLE-1-PERMISSION-1",
                                                ]
                                            ],
                                        "sites"               => [
                                            "10" => [
                                                "roles"       => ["SITE-ROLE-A"],
                                                "permissions" => ["SITE-ROLE-A-PERMISSION-1"]
                                            ],
                                        ],
                                        "organisations"       => [
                                        ],
                                        "siteOrganisationMap" => [
                                            "10" => "1",
                                            "20" => "3",
                                            "30" => "2"
                                        ]
                                    ]
                                ],
                            ]
        ];
        $restClientMock = \DvsaCommonTest\TestUtils\XMock::of(HttpRestJsonClient::class);
        $restClientMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($isCommunicationWorking ? $result : null));

        return $restClientMock;
    }
}
