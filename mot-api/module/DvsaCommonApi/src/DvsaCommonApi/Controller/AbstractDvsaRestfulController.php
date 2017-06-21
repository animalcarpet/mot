<?php

namespace DvsaCommonApi\Controller;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\Http\HttpStatus;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\EmptyRequestBodyException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\UnauthenticatedException;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Log\Logger;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface;
use Zend\View\Model\JsonModel;

/**
 * AbstractDvsaRestfulController.
 */
class AbstractDvsaRestfulController extends AbstractRestfulController
{
    const ERROR_CODE_NOT_ALLOWED = 10;
    const ERROR_CODE_REQUIRED = 20;
    const ERROR_CODE_NOT_FOUND = 40;
    const ERROR_CODE_INVALID_DATA = 60;
    const ERROR_CODE_UNAUTHORIZED = 401;
    const CHECK_POSITIVE_INTEGER = 'positiveInteger';
    const CHECK_POSITIVE_INTEGER_OR_NULL = 'positiveIntegerOrNull';
    const ERROR_GENERIC_MSG = 'An error occurred.';
    const ERROR_MSG_IS_REQUIRED = ' is required';
    const ERROR_MSG_POSITIVE_INTEGER = ' must be a positive integer';
    const ERROR_MSG_POSITIVE_INTEGER_OR_NULL = ' must be a positive integer or null';
    const DISPLAY_MSG_IS_REQUIRED = 'Values are missing.';
    const ERROR_MSG_UNAUTHORIZED_REQUEST = 'Unauthorised request, please supply valid token in authorisation header';

    /**
     * @var \DvsaAuthentication\Identity
     */
    private $identity;

    public function isFeatureEnabled($name)
    {
        return $this
            ->getServiceLocator()
            ->get('Feature\FeatureToggles')
            ->isEnabled($name);
    }

    public function assertFeatureEnabled($name)
    {
        if (!$this->isFeatureEnabled($name)) {
            throw new NotFoundException("Feature '".$name."' is turned off", null, false);
        }
    }

    // Override default actions as they do not return valid JsonModels

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param mixed $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function delete($id)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteList($data)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param mixed $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($id)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function getList()
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param null $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function head($id = null)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function options()
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param $id
     * @param $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function patch($id, $data)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function replaceList($data)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function patchList($data)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * @param mixed $id
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function update($id, $data)
    {
        return $this->returnMethodNotAllowedResponseModel();
    }

    /**
     * Base helper for methods returning Apigility API Problem style responses.
     *
     * See {@http://tools.ietf.org/html/draft-nottingham-http-problem-06} and
     * {@link https://apigility.org/documentation/api-primer/error-reporting} for an explanation of the format used.
     *
     * @param int   $statusCode
     * @param array $properties
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function createApiProblemResponseModel($statusCode, array $properties = [])
    {
        $this->getResponse()->setStatusCode($statusCode);

        $properties = array_merge([
            'type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
            'status' => $statusCode,
            'title' => HttpStatus::$statusTexts[$statusCode],
            'detail' => '',
        ], $properties);

        // Legacy response body
        $errors = ['errors' => [
            'message' => $properties['title'],
            'code' => $statusCode,
            'displayMessage' => $properties['detail'],
            'problem' => $properties,             // Problem Details for HTTP APIs (application/problem+json)
            ],
        ];

        return new JsonModel($errors);
    }

    //TODO please, delete this after removing empty calls
    public function processPostData(RequestInterface $request)
    {
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = $this->decodeJsonIfNotEmpty($request->getContent());
        } else {
            $data = $request->getPost()->toArray();
        }

        return $this->create($data);
    }

    //TODO please, delete this after removing empty calls
    protected function processBodyContent($request)
    {
        $content = $request->getContent();

        // JSON content? decode and return it.
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            return $this->decodeJsonIfNotEmpty($request->getContent());
        }

        parse_str($content, $parsedParams);

        // If parse_str fails to decode, or we have a single element with empty value
        if (!is_array($parsedParams) || empty($parsedParams)
            || (1 == count($parsedParams) && '' === reset($parsedParams))
        ) {
            return $content;
        }

        return $parsedParams;
    }

    //TODO please, delete this after removing empty calls
    private function decodeJsonIfNotEmpty($content)
    {
        if(!empty($content)) {
            return Json::decode($content, $this->jsonDecodeType);
        } else {
            return null;
        }
    }

    /**
     * Helper method to be used when a resource does not exist.
     *
     * @param string $detail
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function createNotFoundResponseModel($detail = 'Resource not found')
    {
        return $this->createApiProblemResponseModel(HttpStatus::HTTP_NOT_FOUND, [
            'detail' => $detail,
        ]);
    }

    /**
     * Helper method to be used when a validation pass fails.
     *
     * See {@link https://apigility.org/documentation/content-validation/validating}.
     *
     * @param array $errors
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function createValidationProblemResponseModel(array $errors)
    {
        return $this->createApiProblemResponseModel(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, [
            'detail' => 'Failed Validation',
            'validation_messages' => $errors,
        ]);
    }

    /**
     * Wraps the parent onDispatch with some request/response logging.
     *
     * @param MvcEvent $e
     *
     * @return mixed|void the parent's response
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if ($routeMatch !== null) {
            $this->logEvent(
                'Received API request to: ['.$routeMatch->getMatchedRouteName().
                '] method: ['.$e->getRequest()->getMethod().'] url: ['.$this->getRequest()->getUriString().'] content: '.$e->getRequest()->getContent(),
                Logger::INFO
            );
        }

        $response = parent::onDispatch($e);

        if ($routeMatch !== null) {
            if ($response instanceof JsonModel) {
                $this->logEvent('Returning json '.$response->serialize());
            } else {
                // We're meant to be always returning JSON, so no reason to end up here
                $this->logEvent('Returning unknown object');
            }
        }
    }

    /**
     * Get the identity of the person making this request.
     *
     * @throws UnauthenticatedException when there is no identity
     *
     * @return \DvsaAuthentication\Identity
     */
    protected function getIdentity()
    {
        // check for cached identity call and return it if set
        if ($this->identity instanceof \DvsaAuthentication\Identity) {
            return $this->identity;
        }

        /** @var \Zend\Authentication\AuthenticationService $service */
        $service = $this->getServiceLocator()->get('DvsaAuthenticationService');
        $identity = $service->getIdentity();
        if (!$identity) {
            throw new UnauthenticatedException();
        }

        $this->identity = $identity;

        return $this->identity;
    }

    /**
     * Convenience method for obtaining the authenticated users' username.
     *
     * @throws UnauthenticatedException when there is no identity
     *
     * @return string
     */
    protected function getUsername()
    {
        return $this->getIdentity()->getUsername();
    }

    /**
     * Convenience method for obtaining the authenticated users' user id.
     *
     * @throws UnauthenticatedException when there is no identity
     *
     * @return int
     */
    protected function getUserId()
    {
        return $this->getIdentity()->getUserId();
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceLocator()->get('Application/Logger');
    }

    /**
     * @return DataCatalogService
     */
    protected function getCatalog()
    {
        return $this->getServiceLocator()->get(DataCatalogService::class);
    }

    /**
     * @param $fieldName
     * @param $postData
     * @param $errors
     * @param null $dataTypeCheck
     *
     * @return array
     */
    protected function checkForRequiredFieldAndAddToErrors($fieldName, $postData, $errors, $dataTypeCheck = null)
    {
        if (!array_key_exists($fieldName, $postData)) {
            $errors[] = [
                'message' => $fieldName.self::ERROR_MSG_IS_REQUIRED,
                'code' => self::ERROR_CODE_REQUIRED,
                'displayMessage' => self::DISPLAY_MSG_IS_REQUIRED,
            ];
        } else {
            if ($dataTypeCheck) {
                $fieldValue = $postData[$fieldName];
                switch ($dataTypeCheck) {
                    case self::CHECK_POSITIVE_INTEGER:
                        if (!is_numeric($fieldValue) || ((int) $fieldValue) < 0) {
                            $message = $fieldName.self::ERROR_MSG_POSITIVE_INTEGER;
                            $errors[] = [
                                'message' => $message,
                                'code' => self::ERROR_CODE_INVALID_DATA,
                                'displayMessage' => $message,
                            ];
                        }
                        break;
                    case self::CHECK_POSITIVE_INTEGER_OR_NULL:
                        if (!is_null($fieldValue) && (!is_numeric($fieldValue) || ((int) $fieldValue) < 0)) {
                            $message = $fieldName.self::ERROR_MSG_POSITIVE_INTEGER_OR_NULL;
                            $errors[] = [
                                'message' => $message,
                                'code' => self::ERROR_CODE_INVALID_DATA,
                                'displayMessage' => $message,
                            ];
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * Allow a message to be added independently of any other checks.
     *
     * @param            $text    String the full error message to add
     * @param            $code    int contains the error code indicator
     * @param int|string $msgType int contains the display message indicator
     *
     * @return array
     */
    protected function makeErrorMessage(
        $text,
        $code = self::ERROR_CODE_REQUIRED,
        $msgType = self::DISPLAY_MSG_IS_REQUIRED
    ) {
        return [
            'message' => $text,
            'code' => $code,
            'displayMessage' => $msgType,
        ];
    }

    /**
     * Add a "required field missing" error message.
     *
     * @param $fieldName String contains the fieldname that is missing
     *
     * @return array
     */
    protected function makeFieldIsRequiredError($fieldName)
    {
        return $this->makeErrorMessage($fieldName.self::ERROR_MSG_IS_REQUIRED);
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    protected function returnMethodNotFoundModel()
    {
        $this->response->setStatusCode(HttpStatus::HTTP_NOT_FOUND);

        return new JsonModel(
            [
                'errors' => [
                        [
                            'message' => HttpStatus::$statusTexts[HttpStatus::HTTP_NOT_FOUND],
                            'code' => self::ERROR_CODE_NOT_FOUND,
                            'displayMessage' => self::ERROR_GENERIC_MSG,
                        ],
                    ],
            ]
        );
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    protected function returnMethodNotAllowedResponseModel()
    {
        $this->response->setStatusCode(405);

        return new JsonModel(
            [
                'errors' => [
                        [
                            'message' => 'Method Not Allowed',
                            'code' => self::ERROR_CODE_NOT_ALLOWED,
                            'displayMessage' => self::ERROR_GENERIC_MSG,
                        ],
                    ],
            ]
        );
    }

    /**
     * @param $errorMessage
     * @param $code
     * @param string $displayMessage
     *
     * @return \Zend\View\Model\JsonModel
     */
    protected function returnBadRequestResponseModel($errorMessage, $code, $displayMessage = self::ERROR_GENERIC_MSG)
    {
        $this->response->setStatusCode(Response::STATUS_CODE_400);

        return new JsonModel(
            [
                'errors' => [
                        [
                            'message' => $errorMessage,
                            'code' => $code,
                            'displayMessage' => $displayMessage,
                        ],
                    ],
            ]
        );
    }

    /**
     * @param $errors
     *
     * @return \Zend\View\Model\JsonModel
     */
    protected function returnBadRequestResponseModelWithErrors($errors)
    {
        $this->response->setStatusCode(Response::STATUS_CODE_400);

        return new JsonModel(['errors' => $errors]);
    }

    /**
     * If logging is enabled then write some logger output.
     *
     * @param $message String
     */
    protected function logEvent($message, $level = Logger::DEBUG)
    {
        static $doLog = null;

        if (is_null($doLog)) {
            $config = $this->getServiceLocator()->get('config');
            $doLog = array_key_exists('logJson', $config) && $config['logJson'];
        }

        if ($doLog) {
            $this->getLogger()->log($level, $message);
        }
    }

    protected function returnDto($dto)
    {
        $dtoSerializer = $this->getDtoSerializer();

        return ApiResponse::jsonOk($dtoSerializer->serialize($dto));
    }

    public function assertContentNotEmpty($content)
    {
        if (empty($content)) {
            throw new EmptyRequestBodyException();
        }
    }

    /**
     * @return DtoReflectiveSerializer
     */
    private function getDtoSerializer()
    {
        return $this->getServiceLocator()->get(DtoReflectiveSerializer::class);
    }

    public function getServiceLocator(){
        // there's no "getErrorHandler" in PHP. It's returned when we set the new one...
        /** @var callable $originalHandler */
        $originalHandler = set_error_handler(function($severity, $message, $file, $line){});

        set_error_handler(function($severity, $message, $file, $line) use ($originalHandler) {
            if(strpos($message, "You are retrieving the service locator from") === 0){
                // that's the error about service loactor
                return;
            }

            // otherwise we log every other error using original handler
            $originalHandler($severity, $message, $file, $line);
        });

        $serviceLocator = parent::getServiceLocator();

        set_error_handler($originalHandler);

        return $serviceLocator;
    }
}
