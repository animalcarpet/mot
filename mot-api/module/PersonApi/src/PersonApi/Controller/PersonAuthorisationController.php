<?php

namespace PersonApi\Controller;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

/**
 * Provides the current users's roles and permissions to the web tier.
 */
class PersonAuthorisationController extends AbstractDvsaRestfulController
{
    /**
     * @var AuthorisationService
     */
    protected $authorisationService;

    public function __construct(AuthorisationService $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    public function get($id)
    {
        /** @var MotIdentityInterface $identity */
        $identity = $this->authorisationService->getIdentity();

        if (is_null($identity)) {
            return ApiResponse::jsonError('Not logged in');
        }

        if (!($identity->getUserId() == $id)) {
            throw new UnauthorisedException(
                'Cannot retrieve authorization for a different user ['.$identity->getUserId().'] vs ['.$id.']'
            );
        }

        return ApiResponse::jsonOk($this->authorisationService->getAuthorizationDataAsArray());
    }
}
