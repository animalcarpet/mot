<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Controller;

use Dvsa\Mot\Api\RegistrationModule\Service\DuplicatedEmailChecker;
use Dvsa\Mot\Api\RegistrationModule\Service\RegistrationService;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractDvsaRestfulController
{
    const KEY_EMAIL = 'email';
    const ERR_MSG_MISSING_KEY = 'Expected "%s" key is missing';

    /**
     * @var RegistrationService
     */
    private $registrationService;

    public function __construct(
        RegistrationService $registrationService
    )
    {
        $this->registrationService = $registrationService;
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        if ($this->registrationService->register($data)) {
            return ApiResponse::jsonOk(
                [
                    'registeredPerson' => [
                        'id' => $this->registrationService->getRegisteredPerson()->getId(),
                        'username' => $this->registrationService->getRegisteredPerson()->getUsername(),
                    ],
                ]
            );
        }

        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_422);

        return ApiResponse::jsonError(
            [$this->registrationService->getMessages()]
        );
    }
}
