<?php

namespace PersonApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Controller\PersonController;
use PersonApi\Service\PersonService;
use DvsaCommon\Dto\Person\PersonDto;

/**
 * Unit tests for PersonController.
 */
class PersonControllerTest extends AbstractPersonControllerTestCase
{
    public function setUp()
    {
        $personServiceMock = XMock::of(PersonService::class, ['getPerson']);
        $personDtoMock = XMock::of(PersonDto::class);
        $personServiceMock->expects($this->once())->method('getPerson')->willReturn($personDtoMock);
        $this->controller = new PersonController($personServiceMock);
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['get']
        );
    }
}
