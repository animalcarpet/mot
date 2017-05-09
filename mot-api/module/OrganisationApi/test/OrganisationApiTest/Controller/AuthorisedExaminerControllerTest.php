<?php

namespace OrganisationApiTest\Controller;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\AuthorisedExaminerController;
use OrganisationApi\Service\AuthorisedExaminerService;
use OrganisationApi\Service\UpdateAeDetailsService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Tests for add, edit and view AuthorisedExaminer.
 */
class AuthorisedExaminerControllerTest extends AbstractRestfulControllerTestCase
{
    const AE_ID = 9876;

    /**
     * @var AuthorisedExaminerService|MockObj
     */
    private $service;

    public function setUp()
    {
        $this->service = XMock::of(AuthorisedExaminerService::class);
        $this->setController(new AuthorisedExaminerController($this->service, XMock::of(UpdateAeDetailsService::class)));

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestActionsResultAndAccess
     */
    public function testActionsResultAndAccess($method, $action, $params, $mocks, $expect)
    {
        //  mock methods of classes
        if ($mocks !== null) {
            $this->mockMethod(
                $this->service, $mocks['method'], $this->once(), $mocks['result'], $mocks['params']
            );
        }

        //  call
        $postParams = ArrayUtils::tryGet($params, 'post', []);
        $putParams = ArrayUtils::tryGet($params, 'put', []);
        $result = $this->getResultForAction($method, $action, $params['route'], null, $postParams, $putParams);

        //  check
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expect['result'], $result);
    }

    public function dataProviderTestActionsResultAndAccess()
    {
        $getServiceResult = new OrganisationDto();
        $getServiceResult->setId(self::AE_ID);

        $jsonOrganisationDto = DtoHydrator::dtoToJson($getServiceResult);

        $postServiceResult = ['id' => self::AE_ID];

        return [
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'route' => ['id' => self::AE_ID],
                ],
                'mocks' => [
                    'method' => 'get',
                    'params' => [self::AE_ID],
                    'result' => $getServiceResult,
                ],
                'expect' => [
                    'result' => [
                        'data' => $jsonOrganisationDto,
                    ],
                ],
            ],
            [
                'method' => 'get',
                'action' => 'getAuthorisedExaminerByNumber',
                'params' => [
                    'route' => ['number' => self::AE_ID],
                ],
                'mocks' => [
                    'method' => 'getByNumber',
                    'params' => [self::AE_ID],
                    'result' => $getServiceResult,
                ],
                'expect' => [
                    'result' => [
                        'data' => $jsonOrganisationDto,
                    ],
                ],
            ],
            [
                'method' => 'post',
                'action' => null,
                'params' => [
                    'route' => null,
                    'post' => [
                        'id' => self::AE_ID,
                        '_class' => OrganisationDto::class,
                    ],
                ],
                'mocks' => [
                    'method' => 'create',
                    'params' => $getServiceResult,
                    'result' => ['id' => self::AE_ID],
                ],
                'expect' => [
                    'result' => [
                        'data' => $postServiceResult,
                    ],
                ],
            ],
        ];
    }
}
