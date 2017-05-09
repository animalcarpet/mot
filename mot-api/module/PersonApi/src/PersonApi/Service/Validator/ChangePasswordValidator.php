<?php

namespace PersonApi\Service\Validator;

use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\Exception\OpenAMClientException;

/**
 * Claim Account Validator.
 */
class ChangePasswordValidator extends AbstractValidator
{
    /**
     * @var array
     */
    private $requiredFields = [
        ChangePasswordInputFilter::FIELD_OLD_PASSWORD,
        ChangePasswordInputFilter::FIELD_PASSWORD,
        ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM,
    ];

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var ChangePasswordInputFilter
     */
    private $changePasswordInputFilter;

    /**
     * @var OpenAMClientInterface
     */
    private $openAMClient;

    private $realm;

    public function __construct(
        MotIdentityProviderInterface $identityProvider,
        ChangePasswordInputFilter $changePasswordInputFilter,
        OpenAMClientInterface $openAMClient,
        $realm
    ) {
        parent::__construct();

        $this->identityProvider = $identityProvider;
        $this->changePasswordInputFilter = $changePasswordInputFilter;
        $this->openAMClient = $openAMClient;
        $this->realm = $realm;
    }

    public function validate(array $data)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty($this->requiredFields, $data);

        $this->changePasswordInputFilter->setData($data);
        if (!$this->changePasswordInputFilter->isValid()) {
            $messages = $this->changePasswordInputFilter->getMessages();
            foreach ($messages as $field => $errors) {
                foreach ($errors as $errorType => $message) {
                    $this->errors->add($field.' '.$message, $field);
                }
            }
        }

        $username = $this->identityProvider->getIdentity()->getUsername();
        $password = $data[ChangePasswordInputFilter::FIELD_OLD_PASSWORD];
        $loginDetails = new OpenAMLoginDetails($username, $password, $this->realm);

        try {
            $this->openAMClient->validateCredentials($loginDetails);
        } catch (OpenAMClientException $e) {
            $this->errors->add(ChangePasswordInputFilter::MSG_PASSWORD_INVALID, ChangePasswordInputFilter::FIELD_OLD_PASSWORD);
        }

        $this->errors->throwIfAny();
    }
}
