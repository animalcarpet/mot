<?php

namespace DvsaCommon\HttpRestJson;

use DvsaCommon\Auth\Http\AuthorizationBearer;
use DvsaCommon\Error\ApiErrorCodes;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Http\HttpStatus;
use DvsaCommon\HttpRestJson\Exception\ForbiddenApplicationException;
use DvsaCommon\HttpRestJson\Exception\ForbiddenOrUnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\OtpApplicationException;
use DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Db\TableGateway\Exception\RuntimeException;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Client\Adapter\Socket;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;
use Zend\Http\Client\Adapter\AdapterInterface;

/**
 * Handles all REST calls to the API and translates any errors received into exceptions.
 */
class  ZendClient implements Client
{
    use EventManagerAwareTrait;

    const CONTENT_TYPE_JSON         = 'application/json; charset=utf-8';
    const CONTENT_TYPE_URL_ENCODING = 'jsonResponse';
    const CONTENT_TYPE_HTML         = 'text/html';
    const CONTENT_TYPE_PDF          = 'application/pdf';
    const CONTENT_TYPE              = 'Content-Type';
    const ACCEPT                    = 'Accept';
    const DEFAULT_API_TIMEOUT       = 30;

    /**
     * @var \Zend\Http\Client
     */
    protected $httpClient;

    /**
     * @var Logger
     */
    protected $logger;

    protected $apiUrl;
    protected $token;
    protected $requestUuid;

    /**
     * @param \Zend\Http\Client $httpClient
     * @param $apiUrl
     * @param null $token
     * @param \Zend\Log\Logger $logger
     * @param null $requestUuid
     * @param int $apiTimeout
     */
    public function __construct(HttpClient $httpClient, $apiUrl, $token = null, Logger $logger = null,
                                $requestUuid = null, $apiTimeout = self::DEFAULT_API_TIMEOUT)
    {
        $this->httpClient  = $httpClient;
        $this->apiUrl      = $apiUrl;
        $this->logger      = $logger;
        $this->token       = $token;
        $this->requestUuid = $requestUuid;

        $this->setApiTimeout($apiTimeout);
    }

    /**
     * Increase the timeout to 30 seconds to allow the backend API talk to CPMS which also talks
     * to 3rd parties when making payment to obtain gateway information
     *
     * @param int $timeout
     */
    private function setApiTimeout($timeout = self::DEFAULT_API_TIMEOUT)
    {
        /** @var Curl | Socket $adapter */
        $adapter = $this->httpClient->getAdapter();
        if ($adapter instanceof AdapterInterface && is_numeric($timeout)) {
            $options = $adapter->getConfig();
            if ($options['timeout'] < $timeout) {
                $options['timeout'] = (int)$timeout;
                $this->httpClient->setOptions($options);
            }
        }
    }

    /**
     * @param $token
     */
    public function setAccessToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param $resourcePath
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return \DvsaCommon\Dto\AbstractDataTransferObject
     */
    public function get($resourcePath)
    {
        return DtoHydrator::jsonToDto($this->dispatchRequestAndDecodeResponse($resourcePath, "GET"));
    }

    /**
     * @param $resourcePath
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return mixed|string
     */
    public function getPdf($resourcePath)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "GET", self::CONTENT_TYPE_PDF);
    }

    /**
     * @param $resourcePath
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return mixed|string
     */
    public function getHtml($resourcePath)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "GET", self::CONTENT_TYPE_URL_ENCODING);
    }

    /**
     * @param $resourcePath
     * @param $params
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return mixed|string
     */
    public function getWithParams($resourcePath, $params)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "GET", self::CONTENT_TYPE_URL_ENCODING, $params);
    }

    /**
     * @param $resourcePath
     * @param $params
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return \DvsaCommon\Dto\AbstractDataTransferObject
     */
    public function getWithParamsReturnDto($resourcePath, $params)
    {
        return DtoHydrator::jsonToDto(
            $this->dispatchRequestAndDecodeResponse($resourcePath, "GET", self::CONTENT_TYPE_URL_ENCODING, $params)
        );
    }

    /**
     * @param $resourcePath
     * @param array $data
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return mixed|string
     */
    public function post($resourcePath, $data = [])
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "POST", self::CONTENT_TYPE_JSON, $data);
    }

    /**
     * @param $resourcePath
     * @param array $data
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return mixed|string
     */
    public function patch($resourcePath, $data = [])
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, 'PATCH', self::CONTENT_TYPE_JSON, $data);
    }

    /**
     * @deprecated All requests are now application/json, use post()
     */
    public function postJson($resourcePath, $data)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "POST", self::CONTENT_TYPE_JSON, $data);
    }

    /**
     * @param $resourcePath
     * @param $data
     *
     * @return mixed
     */
    public function put($resourcePath, $data)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "PUT", self::CONTENT_TYPE_JSON, $data);
    }

    /**
     * @deprecated All requests are now application/json, use put()
     */
    public function putJson($resourcePath, $data)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "PUT", self::CONTENT_TYPE_JSON, $data);
    }

    /**
     * @param $resourcePath
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     *
     * @return mixed|string
     */
    public function delete($resourcePath)
    {
        return $this->dispatchRequestAndDecodeResponse($resourcePath, "DELETE");
    }

    /**
     * @param $resourcePath
     * @param $method
     * @param string $contentType
     * @param null   $data
     * @param array  $headers
     *
     * @throws \DvsaCommon\Exception\UnauthorisedException
     * @throws \DvsaCommon\HttpRestJson\Exception\ForbiddenApplicationException
     * @throws \DvsaCommon\HttpRestJson\Exception\ForbiddenOrUnauthorisedException
     * @throws \DvsaCommon\HttpRestJson\Exception\GeneralRestException
     * @throws \DvsaCommon\HttpRestJson\Exception\NotFoundException
     * @throws \DvsaCommon\HttpRestJson\Exception\OtpApplicationException
     * @throws \DvsaCommon\HttpRestJson\Exception\RestServiceUnexpectedContentTypeException
     * @throws \DvsaCommon\HttpRestJson\Exception\ValidationException
     *
     * @return mixed|string
     */
    private function dispatchRequestAndDecodeResponse($resourcePath, $method,
                                                      $contentType = self::CONTENT_TYPE_URL_ENCODING, $data = null,
                                                      $headers = [])
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $headers['X-calling-uri'] = $_SERVER['REQUEST_URI'];
        }
        if (isset($this->requestUuid)) {
            $headers['X-request-uuid'] = $this->requestUuid;
        }
        $url     = $this->apiUrl . $resourcePath;
        $request = new Request();

        switch ($contentType) {
            case self::CONTENT_TYPE_JSON:
                $headers[self::CONTENT_TYPE] = self::CONTENT_TYPE_JSON;
                $headers[self::ACCEPT]       = self::CONTENT_TYPE_JSON;
                break;
            case self::CONTENT_TYPE_HTML:
                $headers[self::CONTENT_TYPE] = 'application/x-www-form-urlencoded; charset=UTF-8';
                $headers[self::ACCEPT]       = self::CONTENT_TYPE_HTML;
                break;
            case self::CONTENT_TYPE_PDF:
                $headers[self::CONTENT_TYPE] = 'application/x-www-form-urlencoded; charset=UTF-8';
                $headers[self::ACCEPT]       = self::CONTENT_TYPE_PDF;
                break;
            case self::CONTENT_TYPE_URL_ENCODING:
                $headers[self::CONTENT_TYPE] = 'application/x-www-form-urlencoded; charset=UTF-8';
                $headers[self::ACCEPT]       = self::CONTENT_TYPE_JSON;
                break;
            default:
                throw new \RuntimeException("Unsupported Content Type [" . print_r($contentType, true) . ']');
        }
        if ($this->token) {
            $request->getHeaders()->addHeader(new AuthorizationBearer($this->token));
        }
        $request->getHeaders()->addHeaders($headers);
        $request->setUri($url);
        $request->setMethod($method);

        if ($contentType == self::CONTENT_TYPE_JSON && $method != "GET" && $data) {
            $request->setContent(json_encode($data));
        }

        if ($method == "GET" && $data) {
            $request->setQuery(new Parameters($data));
        }

        $this->logStartOfRequest($resourcePath, $method, $request);
        $this->getEventManager()->trigger(
            'startOfRequest', null,
            ['resourcePath' => $resourcePath, 'method' => $method,
                'content'      => $request->getContent(), ]
        );
        $startTime = microtime(true);

        /** @var \Zend\Http\Response $response */
        $response = $this->httpClient->dispatch($request);
        //Reset the request after every call to make sure we don't persist the API session.
        $this->httpClient->resetParameters();
        $this->httpClient->clearCookies();

        $statusCode = $response->getStatusCode();

        $respContentType    = $response->getHeaders()->get(self::CONTENT_TYPE);
        $respContentTypeVal = null;
        if ($respContentType instanceof ContentType) {
            $respContentTypeVal = $respContentType->getFieldValue();
        }

        $this->logEndOfRequest($resourcePath, $method, $startTime, $statusCode, $response);
        $this->getEventManager()->trigger(
            'endOfRequest', null,
            [$resourcePath, $method, $startTime, $statusCode, $response]
        );

        if (in_array($respContentTypeVal, [self::CONTENT_TYPE_JSON, self::CONTENT_TYPE_URL_ENCODING])) {
            $responseBody = $this->decodeJsonOrThrowException($resourcePath, $method, $data, $response);
        } else {
            $responseBody = $response->getBody();
        }

        if ($statusCode !== 200) { // Not all Non 200 status code means something went wrong
            $this->handleNon200($resourcePath, $method, $data, $response, $responseBody, $statusCode, $request);
        }

        if ($respContentTypeVal !== null && $contentType) {
            if (in_array($contentType, [self::CONTENT_TYPE_JSON, self::CONTENT_TYPE_URL_ENCODING])) {
                if ($respContentTypeVal != self::CONTENT_TYPE_JSON) {
                    throw new RestServiceUnexpectedContentTypeException(
                        $resourcePath,
                        $method,
                        $data,
                        $statusCode,
                        null,
                        $request->getHeaders(),
                        $response->getHeaders()
                    );
                }
            } else {
                if ($respContentTypeVal != $contentType) {
                    throw new RestServiceUnexpectedContentTypeException(
                        $resourcePath,
                        $method,
                        $data,
                        $statusCode,
                        null,
                        $request->getHeaders(),
                        $response->getHeaders()
                    );
                }
            }
        }

        return $responseBody;
    }

    /**
     * Handle every response with a HTTP code other than 200 OK.
     *
     * @param $resourcePath
     * @param $method
     * @param $data
     * @param \Zend\Http\Response $response
     * @param $responseBody
     * @param $statusCode
     * @param \Zend\Http\Request  $request
     *
     * @throws \DvsaCommon\Exception\UnauthorisedException
     * @throws \DvsaCommon\HttpRestJson\Exception\ForbiddenApplicationException
     * @throws \DvsaCommon\HttpRestJson\Exception\ForbiddenOrUnauthorisedException
     * @throws \DvsaCommon\HttpRestJson\Exception\GeneralRestException
     * @throws \DvsaCommon\HttpRestJson\Exception\NotFoundException
     * @throws \DvsaCommon\HttpRestJson\Exception\OtpApplicationException
     * @throws \DvsaCommon\HttpRestJson\Exception\ValidationException
     */
    private function handleNon200($resourcePath, $method, $data, Response $response, $responseBody, $statusCode,
                                  Request $request)
    {
        $errorData = $this->extractArrayIfPresent($responseBody, 'errorData');
        $errors    = $this->extractArrayIfPresent($responseBody, 'errors');

        if (!is_null($errors)) {
            switch ($statusCode) {
                case 400:
                    throw new ValidationException($resourcePath, $method, $data, $statusCode, $errors, $errorData);
                case 401:
                    if ($this->isErrorLike($errors, ApiErrorCodes::OTP_ERROR)) {
                        throw new OtpApplicationException(
                            $resourcePath,
                            $method,
                            $data,
                            $statusCode,
                            $errors,
                            $errorData
                        );
                    } else {
                        throw new ForbiddenOrUnauthorisedException(
                            $resourcePath,
                            $method,
                            $data,
                            $statusCode
                        );
                    }
                case 403:
                    /*
                    * Need to distinguish between 403 which was returned by application code, vs
                    * 403 which was returned by the framework or Apache. Just look at whether
                    * the "errors" JSON element was populated.
                    */
                    if ($this->isErrorLike($errors, ApiErrorCodes::OTP_ERROR)) {
                        throw new OtpApplicationException(
                            $resourcePath,
                            $method,
                            $data,
                            $statusCode,
                            $errors,
                            $errorData
                        );
                    } elseif ($this->isErrorLike($errors, ApiErrorCodes::UNAUTHORISED)) {
                        $debugInfo = ArrayUtils::tryGet($responseBody, 'debugInfo');
                        $message   = ArrayUtils::get($errors[0], 'message');
                        throw (new UnauthorisedException($message))->setDebugInfo($debugInfo);
                    } else {
                        throw new ForbiddenApplicationException(
                            $resourcePath, $method, $data, $statusCode, $errors, $errorData
                        );
                    }
                // always throws
                case 404:
                    $errMsg = (empty($errors) ? null : $errors[0]['message']);
                    throw new NotFoundException($resourcePath, $method, $data, $statusCode, $errMsg);

                case HttpStatus::HTTP_UNPROCESSABLE_ENTITY: // 422
                    // Error caused by a failed validation pass.
                    // See https://apigility.org/documentation/content-validation/validating
                    throw new ValidationException($resourcePath, $method, $data, $statusCode, $errors, $errorData);

                // always throws
                default:
                    $errMsg = [];
                    foreach ($errors as $err) {
                        $errMsg[] = $err['message'];
                    }

                    throw new GeneralRestException(
                        $resourcePath,
                        $method,
                        $data,
                        $statusCode,
                        (!empty($errMsg) ? implode('; ', $errMsg) : null),
                        $request->getHeaders(),
                        $response->getHeaders()
                    );
            }
        } else {
            switch ($statusCode) {
                case 401:
                case 403:
                    throw new ForbiddenOrUnauthorisedException($resourcePath, $method, $data, $statusCode);

                case HttpStatus::HTTP_UNPROCESSABLE_ENTITY: // 422
                    // Error caused by a failed validation pass.
                    // See https://apigility.org/documentation/content-validation/validating
                    throw new ValidationException($resourcePath, $method, $data, $statusCode, $errors, $errorData);

                default:
                    throw new GeneralRestException(
                        $resourcePath,
                        $method,
                        $data,
                        $statusCode,
                        null,
                        $request->getHeaders()
                    );
            }
        }
    }

    /**
     * @param $resourcePath
     * @param $method
     * @param \Zend\Http\Request $request
     */
    private function logStartOfRequest($resourcePath, $method, Request $request)
    {
        if ($this->logger) {
            $this->logger->log(Logger::DEBUG, "Sending request to REST API, $resourcePath : $method");
            if ($request->getContent()) {
                $this->logger->log(Logger::DEBUG, "REST request content is [" . $request->getContent() . "]");
            }
        }
    }

    /**
     * @param $resourcePath
     * @param $method
     * @param $startTime
     * @param $statusCode
     * @param $response
     */
    private function logEndOfRequest($resourcePath, $method, $startTime, $statusCode, $response)
    {
        if ($this->logger) {
            $requestTimeInMilliseconds = round(microtime(true) - $startTime, 3) * 1000;
            $this->logger->log(
                Logger::INFO,
                "Finished request to REST API, duration='$requestTimeInMilliseconds', $resourcePath : $method"
            );

            $logLevel = Logger::DEBUG;

            if ($statusCode >= 400) {
                $logLevel = Logger::WARN;
            }
            $this->logger->log($logLevel, "REST response content was [" . $response->getBody() . "]");
        }
    }

    /**
     * @param $decodedResponseBody
     * @param $str
     */
    private function extractArrayIfPresent($decodedResponseBody, $str)
    {
        if (!empty($decodedResponseBody[$str]) && is_array($decodedResponseBody[$str])) {
            return $decodedResponseBody[$str];
        }

        return;
    }

    /**
     * @param $resourcePath
     * @param $method
     * @param $data
     * @param \Zend\Http\Response $response
     *
     * @throws \DvsaCommon\HttpRestJson\Exception\GeneralRestException
     *
     * @return mixed
     */
    private function decodeJsonOrThrowException($resourcePath, $method, $data, Response $response)
    {
        $decodedResponseBody = json_decode($response->getBody(), true);

        if (!$decodedResponseBody) {
            throw new GeneralRestException(
                $resourcePath, $method, $data, $response->getStatusCode(),
                "JSON decoding failed; response body [" . $response->getBody() . "]"
            );
        }

        return $decodedResponseBody;
    }

    /**
     * @param $errors
     * @param $errorCode
     *
     * @return bool
     */
    private function isErrorLike($errors, $errorCode)
    {
        foreach ($errors as $error) {
            if (isset($error['code']) && $error['code'] === $errorCode) {
                return true;
            }
        }

        return false;
    }
}
