<?php

namespace AccountApi\Service;

use AccountApi\Service\Exception\OpenAmChangePasswordException;
use Dvsa\OpenAM\Model\OpenAMExistingIdentity;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaCommonApi\Service\Exception\ServiceException;
use PersonApi\Service\PasswordExpiryNotificationService;

/**
 * Wrapper for the OpenAMClient service with administrator credentials to perform password changes and account
 * unlocking operations.
 */
class OpenAmIdentityService
{
    /**
     * @var OpenAMClientInterface
     */
    private $openAMClient;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var PasswordExpiryNotificationService
     */
    private $passwordExpiryNotificationService;

    /**
     * @param OpenAMClientInterface             $openAMClient
     * @param PasswordExpiryNotificationService $passwordExpiryNotificationService
     * @param $realm
     */
    public function __construct(
        OpenAMClientInterface $openAMClient,
        PasswordExpiryNotificationService $passwordExpiryNotificationService,
        $realm
    ) {
        $this->openAMClient = $openAMClient;
        $this->realm = $realm;
        $this->passwordExpiryNotificationService = $passwordExpiryNotificationService;
    }

    /**
     * @param string $username
     * @param string $newPassword
     *
     * @throws OpenAmChangePasswordException
     */
    public function changePassword($username, $newPassword)
    {
        $identity = new OpenAMExistingIdentity($username, $this->realm, ['userpassword' => $newPassword]);
        try {
            $this->openAMClient->updateIdentity($identity);
        } catch (\Exception $e) {
            throw new OpenAmChangePasswordException($e->getMessage(), ServiceException::DEFAULT_STATUS_CODE, $e);
        }

        $this->passwordExpiryNotificationService->remove($username);
    }

    /**
     * @param string $username
     *
     * @return bool
     */
    public function unlockAccount($username)
    {
        $userDetails = new OpenAMLoginDetails($username, null, $this->realm);
        $status = $this->openAMClient->unlockAccount($userDetails);

        return $status;
    }
}
