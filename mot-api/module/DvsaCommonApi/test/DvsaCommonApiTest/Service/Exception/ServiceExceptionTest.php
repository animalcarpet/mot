<?php

namespace DvsaCommonApiTest\Service\Exception;

use DvsaCommonApi\Service\Exception\ServiceException;
use PHPUnit_Framework_TestCase;

/**
 * Class ServiceExceptionTest.
 */
class ServiceExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $message = 'error';
        $statusCode = 501;
        $serviceException = new ServiceException($message, $statusCode);

        $this->assertEquals($message, $serviceException->getMessage());
        $this->assertEquals($statusCode, $serviceException->getCode());
    }

    public function testConstructorWithDefaults()
    {
        $message = 'error';
        $serviceException = new ServiceException($message);

        $this->assertEquals($message, $serviceException->getMessage());
        $this->assertEquals(ServiceException::DEFAULT_STATUS_CODE, $serviceException->getCode());
    }

    public function testInitialValues()
    {
        $serviceException = new ServiceException('error');

        $this->assertEquals([], $serviceException->getErrors());
    }

    public function testAddError()
    {
        $serviceException = new ServiceException('error');

        $errorMessage = 'error';
        $code = 11;
        $displayMessage = 'an error';
        $serviceException->addError($errorMessage, $code, $displayMessage);

        $expectedErrors = [[
            'message' => $errorMessage,
            'code' => $code,
            'displayMessage' => $displayMessage,
        ]];

        $this->assertEquals($expectedErrors, $serviceException->getErrors());
    }

    public function testAddErrorWithDefaults()
    {
        $serviceException = new ServiceException('error');

        $errorMessage = 'error';
        $code = 11;
        $serviceException->addError($errorMessage, $code);

        $expectedErrors = [[
            'message' => $errorMessage,
            'code' => $code,
            'displayMessage' => ServiceException::ERROR_GENERIC_MSG,
        ]];

        $this->assertEquals($expectedErrors, $serviceException->getErrors());
    }

    public function testCreateError()
    {
        $errorMessage = 'error';
        $code = 11;

        $expectedError = [
            'message' => $errorMessage,
            'code' => $code,
            'displayMessage' => ServiceException::ERROR_GENERIC_MSG,
        ];

        $this->assertEquals($expectedError, ServiceException::createError($errorMessage, $code));
    }

    public function testGetJsonModel()
    {
        $serviceException = new ServiceException('error');

        $errorMessage = 'error';
        $code = 11;
        $serviceException->addError($errorMessage, $code);

        $expectedErrors = [[
            'message' => $errorMessage,
            'code' => $code,
            'displayMessage' => ServiceException::ERROR_GENERIC_MSG,
        ]];

        $jsonModel = $serviceException->getJsonModel();
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $jsonModel);
        $errors = $jsonModel->getVariable('errors');
        $this->assertEquals($expectedErrors, $errors);
    }

    public function testArrayMergeRecursiveDistinct()
    {
        $serviceException = new ServiceException('error');

        // Test flat post args with string keys
        $array1 = ['foo' => 1, 'bar' => 's1'];
        $array2 = ['baz' => 2, 'bat' => 's2'];
        $expected = [
                'foo' => 1,
                'bar' => 's1',
                'baz' => 2,
                'bat' => 's2',
              ];
        $actual = $serviceException->arrayMergeRecursiveDistinct($array1, $array2);
        $this->assertEquals($expected, $actual);

        // Test overlapping string keys
        $array1 = ['foo' => 1, 'bar' => 's1'];
        $array2 = ['foo' => 2, 'bat' => 's2'];
        $expected = [
            'foo' => [1, 2],
            'bar' => 's1',
            'bat' => 's2',
        ];
        $actual = $serviceException->arrayMergeRecursiveDistinct($array1, $array2);
        $this->assertEquals($expected, $actual);

        // Test flat args with overlapping numeric keys
        $array1 = [0 => 'foo', 1 => 'bar'];
        $array2 = [0 => 'bar', 3 => 'bat'];
        $expected = [
            0 => ['foo', 'bar'],
            1 => 'bar',
            3 => 'bat',
        ];
        $actual = $serviceException->arrayMergeRecursiveDistinct($array1, $array2);
        $this->assertEquals($expected, $actual);

        // This is an enforcement use case, that has triggered us to ditch the in-built
        // array_merge_recursive for this function.
        $array1 = [
            'rfrs' => [
                '2035' => [
                    'justification' => 0,
                ],
            ],
        ];

        $array2 = [
            'rfrs' => [
                '2031' => [
                    'justification' => 1,
                ],
            ],
        ];

        $expected = [
            'rfrs' => [
                2035 => ['justification' => 0],
                2031 => ['justification' => 1],
            ],
        ];

        $actual = $serviceException->arrayMergeRecursiveDistinct($array1, $array2);

        // This re-indexes the rfrs ids in the second array.
        $inbuiltActual = array_merge_recursive($array1, $array2);
        $this->assertNotEquals($expected, $inbuiltActual);
        $this->assertEquals($expected, $actual);
    }
}
