<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonService;

/**
 * Class ResetPinController.
 */
class ResetPinController extends AbstractDvsaRestfulController
{
    const FIELD_PASSWORD = 'password';

    /**
     * @var PersonService
     */
    protected $personService;

    public function __construct(PersonService $service)
    {
        $this->personService = $service;
    }

    public function update($id, $data)
    {
        if ((int) $id !== $this->getIdentity()->getUserId()) {
            $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_400);

            return ApiResponse::jsonError(['message' => 'Can only reset your own PIN']);
        }

        $pin = $this->personService->regeneratePinForPerson($id);

        return ApiResponse::jsonOk(['pin' => $pin]);
    }
}
