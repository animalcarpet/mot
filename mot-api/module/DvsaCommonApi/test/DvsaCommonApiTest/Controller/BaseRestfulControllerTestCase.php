<?php

namespace DvsaCommonApiTest\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Response;

/**
 * Class BaseRestfulControllerTestCase.
 */
abstract class BaseRestfulControllerTestCase extends PHPUnit_Framework_TestCase
{
    const HTTP_OK_CODE = 200;
    const HTTP_ERR_400 = 400;
    const HTTP_ERR_403 = 403;
    const HTTP_ERR_404 = 404;
    const HTTP_ERR_500 = 500;
    const HTTP_REDIRECT_302 = 302;
    const HTTP_ERR_FORBIDDEN = 403;

    use TestCaseTrait;

    protected function assertResponseStatusAndResultHasError(
        $response,
        $expectedResponseStatus,
        $result,
        $expectedErrorMessage,
        $expectedErrorCode
    ) {
        $this->assertResponseStatusAndResultHasErrors(
            $response,
            $expectedResponseStatus,
            $result,
            [
                [
                    'message' => $expectedErrorMessage,
                    'code' => $expectedErrorCode,
                ],
            ]
        );
    }

    protected function assertResponseStatusAndResultHasErrors(
        Response $response,
        $expectedResponseStatus,
        $result,
        $errorArray
    ) {
        $this->assertEquals($expectedResponseStatus, $response->getStatusCode());
        $this->assertInstanceOf("Zend\View\Model\JsonModel", $result);
        $vars = $result->getVariables();
        $this->assertTrue(array_key_exists('errors', $vars), 'Should have errors');

        foreach ($errorArray as $error) {
            $this->assertEquals($error['message'], $vars['errors'][0]['message']);
            $this->assertEquals($error['code'], $vars['errors'][0]['code']);
            $this->assertTrue(array_key_exists('displayMessage', $vars['errors'][0]), 'Should have display message');
        }
    }

    protected function assertResponse405Error($response, $result)
    {
        $this->assertResponseStatusAndResultHasError(
            $response,
            405,
            $result,
            'Method Not Allowed',
            AbstractDvsaRestfulController::ERROR_CODE_NOT_ALLOWED
        );
    }

    protected function assertResponse401Error($response, $result)
    {
        $this->assertResponseStatusAndResultHasError(
            $response,
            401,
            $result,
            AbstractDvsaRestfulController::ERROR_MSG_UNAUTHORIZED_REQUEST,
            AbstractDvsaRestfulController::ERROR_CODE_UNAUTHORIZED
        );
    }

    protected function assertResponse403ErrorForRequiredRole($response, $result, $requiredRole)
    {
        $this->assertResponseStatusAndResultHasError(
            $response,
            403,
            $result,
            'Unauthorised request, requires role '.$requiredRole.' to perform that action',
            ForbiddenException::ERROR_CODE_FORBIDDEN
        );
    }
}
