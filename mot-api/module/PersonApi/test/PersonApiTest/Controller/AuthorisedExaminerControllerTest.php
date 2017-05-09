<?php

namespace PersonApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Service\AuthorisedExaminerService;
use PersonApi\Controller\AuthorisedExaminerController;

/**
 * Unit tests for AuthorisedExaminerController.
 */
class AuthorisedExaminerControllerTest extends AbstractPersonControllerTestCase
{
    public function setUp()
    {
        $authorisedExaminerMock = XMock::of(AuthorisedExaminerService::class);
        $this->controller = new AuthorisedExaminerController($authorisedExaminerMock);
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['get']
        );
    }
}
