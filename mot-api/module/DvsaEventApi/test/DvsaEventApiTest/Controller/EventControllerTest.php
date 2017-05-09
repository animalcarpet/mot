<?php

namespace DvsaEventApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventListDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaEventApi\Controller\EventController;
use DvsaEventApi\Service\EventService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;

class EventControllerTest extends AbstractMotApiControllerTestCase
{
    const AE_ID = 2;
    const AE_TYPE = 'ae';
    const EVENT_ID = 1;

    private $eventServiceMock;

    public function setUp()
    {
        $this->setController(new EventController());

        parent::setUp();

        $this->eventServiceMock = XMock::of(EventService::class, ['getList', 'get']);
        $this->serviceManager->setService(EventService::class, $this->eventServiceMock);
    }

    /**
     * Test access for specified action and parameters.
     *
     * @param string $method        HTTP request type (get, post, put)
     * @param array  $id            Route id
     * @param array  $type          Route type
     * @param string $serviceMethod Service method name
     * @param string $serviceReturn Service method will return
     * @param array  $expectResult  Expected result
     *
     * @dataProvider dataProviderTestCreateCanAccessed
     */
    public function testCreateCanAccessed(
        $method,
        $id,
        $type,
        $serviceMethod,
        $serviceReturn,
        $expectResult
    ) {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        //  --  mock    --
        $this->eventServiceMock->expects($this->once())
            ->method($serviceMethod)
            ->willReturn($serviceReturn);

        $this->routeMatch->setParam('id', $id);
        $this->routeMatch->setParam('type', $type);
        $this->request->setMethod($method);
        $result = $this->controller->dispatch($this->request);

        //  --  check --
        if (isset($expectResult['exception'])) {
            $error = $result['errors'][0];

            $this->assertResponseStatusAndResultHasError(
                $this->getController()->getResponse(),
                $expectResult['statusCode'],
                $result,
                $error['message'],
                $error['code']
            );
        } else {
            $this->assertResponseStatusAndResult($expectResult['statusCode'], $expectResult['result'], $result);
        }
    }

    public function dataProviderTestCreateCanAccessed()
    {
        $hydrator = new DtoHydrator();

        $dto = (new EventListDto())
            ->setOrganisationId(self::AE_ID)
            ->setEvents(
                [
                (new EventDto())
                    ->setDate('date')
                    ->setType('type')
                    ->setDescription('description'),
                ]
            );

        return [
            [
                'method' => 'post',
                'id' => self::AE_ID,
                'type' => self::AE_TYPE,
                'serviceMethod' => 'getList',
                'serviceReturn' => $dto,
                'expectResult' => [
                    'statusCode' => self::HTTP_OK_CODE,
                    'result' => ['data' => $hydrator->extract($dto)],
                ],
            ],
        ];
    }

    /**
     * Test access for specified action and parameters.
     */
    public function testGetCanAccessed()
    {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        //  --  mock    --
        $this->eventServiceMock->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->routeMatch->setParam('id', self::EVENT_ID);
        $this->request->setMethod('get');
        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(200, ['data' => []], $result);
    }
}
