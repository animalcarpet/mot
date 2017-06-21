<?php

namespace AccountApiTest\Controller;

use AccountApi\Service\ClaimService;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use AccountApi\Controller\ClaimController;
use Zend\Http\PhpEnvironment\Request;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class ClaimControllerTest.
 */
class ClaimControllerTest extends AbstractRestfulControllerTestCase
{
    const PIN = '123456';
    const PERSON_ID = 1;

    /**
     * @var ClaimService|MockObj
     */
    private $service;

    protected function setUp()
    {
        $this->service = XMock::of(ClaimService::class);

        $this->setController(new ClaimController($this->service));

        parent::setUp();

        $this->serviceManager->setService(ClaimService::class, $this->service);
    }

    public function testClaimControllerGet()
    {
        $this->mockMethod($this->service, 'generateClaimAccountData', $this->once(), self::PIN);

        $result = $this->getResultForAction('get', null, ['id' => self::PERSON_ID]);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => self::PIN], $result);
    }

    public function testClaimControllerUpdate()
    {
        $this->mockMethod($this->service, 'save', $this->once(), true);

        $result = $this->getResultForAction(Request::METHOD_PUT, null, ['id' => self::PERSON_ID], [], [], ["ss" => "aa"]);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => true], $result);
    }
}
