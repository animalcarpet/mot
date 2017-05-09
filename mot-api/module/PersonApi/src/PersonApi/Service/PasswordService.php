<?php

namespace PersonApi\Service;

use AccountApi\Service\OpenAmIdentityService;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use PersonApi\Service\Validator\ChangePasswordValidator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Exception\UnauthorisedException;
use AccountApi\Service\Exception\OpenAmChangePasswordException;
use DvsaCommonApi\Service\Validator\ErrorSchema;

class PasswordService
{
    const MSG_NOT_AUTHORISED = 'You are not authorised to access this resource';

    /** @var ChangePasswordValidator */
    private $changePasswordValidator;
    /** @var MotIdentityProviderInterface */
    private $identityProvider;
    /** @var OpenAmIdentityService */
    private $openAmIdentityService;

    public function __construct(
        ChangePasswordValidator $changePasswordValidator,
        MotIdentityProviderInterface $identityProvider,
        OpenAmIdentityService $openAmIdentityService
    ) {
        $this->changePasswordValidator = $changePasswordValidator;
        $this->identityProvider = $identityProvider;
        $this->openAmIdentityService = $openAmIdentityService;
    }

    public function changePassword($personId, array $data)
    {
        if ($this->identityProvider->getIdentity()->getUserId() !== $personId) {
            throw new UnauthorisedException(self::MSG_NOT_AUTHORISED);
        }

        $this->changePasswordValidator->validate($data);

        $username = $this->identityProvider->getIdentity()->getUsername();
        $password = $data[ChangePasswordInputFilter::FIELD_PASSWORD];
        try {
            $this->openAmIdentityService->changePassword($username, $password);
        } catch (OpenAmChangePasswordException $e) {
            $error = new ErrorSchema();
            $error->throwError(ChangePasswordInputFilter::MSG_PASSWORD_HISTORY, ChangePasswordInputFilter::FIELD_PASSWORD);
        }
    }
}
