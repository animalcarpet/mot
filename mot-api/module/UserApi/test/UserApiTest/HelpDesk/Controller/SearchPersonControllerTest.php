<?php

namespace UserApiTest\HelpDesk\Controller;

use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommonApiTest\Controller\ApiControllerUnitTestInterface;
use DvsaCommonApiTest\Controller\ApiControllerUnitTestTrait;
use DvsaCommonTest\Dto\Person\SearchPersonResultDtoTest;
use UserApi\HelpDesk\Controller\SearchPersonController;
use UserApi\HelpDesk\Service\HelpDeskPersonService;
use Zend\Stdlib\Parameters;

/**
 * Unit tests for SearchPersonController.
 */
class SearchPersonControllerTest extends \PHPUnit_Framework_TestCase implements ApiControllerUnitTestInterface
{
    use ApiControllerUnitTestTrait;

    public function setUp()
    {
        $this->controller = new SearchPersonController();
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->request->setQuery(new Parameters(['username' => 'aaa']));

        $this->assertMethodsOk(
            ['getList']
        );
    }

    public function mockServices()
    {
        $searchPersonServiceMock = $this->createMock(HelpDeskPersonService::class);

        $searchPersonServiceMock->expects($this->any())
            ->method('search')
            ->will(
                $this->returnValue(new SearchPersonResultDto(SearchPersonResultDtoTest::getSearchPersonResultDtoData()))
            );
    }
}
