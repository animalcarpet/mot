<?php

namespace DvsaAuthentication\Login\OpenAM;

use Dvsa\OpenAM\Exception\InvalidPasswordException;
use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\Exception\OpenAMUnauthorisedException;
use Dvsa\OpenAM\Exception\TooManyAuthenticationAttemptsException;
use Dvsa\OpenAM\Exception\UserInactiveException;
use Dvsa\OpenAM\Exception\UserLockedException;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthentication\Identity\IdentityByTokenResolver;
use DvsaAuthentication\Identity\OpenAM\OpenAMIdentityByTokenResolver;
use DvsaAuthentication\Login\Response\AccountLockedAuthenticationFailure;
use DvsaAuthentication\Login\Response\AuthenticationSuccess;
use DvsaAuthentication\Login\Response\GenericAuthenticationFailure;
use DvsaAuthentication\Login\Response\InvalidCredentialsAuthenticationFailure;
use DvsaAuthentication\Login\Response\LockoutWarningAuthenticationFailure;
use DvsaAuthentication\Login\Response\UnresolvableIdentityAuthenticationFailure;
use DvsaAuthentication\Login\UsernamePasswordAuthenticator;
use Zend\Log\LoggerInterface;

/**
 * Authenticates a user identified by username and password with OpenAM
 */
class OpenAMAuthenticator implements UsernamePasswordAuthenticator
{
    private $openAMClient;

    private $openAMOptions;

    private $identityByTokenResolver;

    private $logger;

    public function __construct(
        OpenAMClientInterface $openAMClient,
        OpenAMClientOptions $openAMOptions,
        OpenAMIdentityByTokenResolver $identityByTokenResolver,
        LoggerInterface $logger

    ) {
        $this->openAMClient = $openAMClient;
        $this->openAMOptions = $openAMOptions;
        $this->identityByTokenResolver = $identityByTokenResolver;
        $this->logger = $logger;
    }


    public function authenticate($username, $password)
    {
        if (false === $this->checkCredentials($username, $password)) {
            $this->logger->debug('Empty username or/and password', [$username]);
            return new InvalidCredentialsAuthenticationFailure();
        }

        $realm = $this->openAMOptions->getRealm();
        $loginDetails = new OpenAMLoginDetails($username, $password, $realm);
        try {
            $this->logger->debug(sprintf('Authenticating user "%s"', $username));
            $token = $this->openAMClient->authenticate($loginDetails);

        } catch (OpenAMClientException $ex) {
            return $this->translateExceptionToAuthenticationResult($ex, $username);
        }

        $resolvedIdentity = $this->identityByTokenResolver->resolve($token);
        if (is_null($resolvedIdentity)) {
            return new UnresolvableIdentityAuthenticationFailure();
        }

        return new AuthenticationSuccess($resolvedIdentity);
    }

    public function validateCredentials($username, $password)
    {
        if (false === $this->checkCredentials($username, $password)) {
            return false;
        }

        $realm = $this->openAMOptions->getRealm();
        $loginDetails = new OpenAMLoginDetails($username, $password, $realm);

        try {
            return $this->openAMClient->validateCredentials($loginDetails);
        } catch (OpenAMClientException $ex) {
            return false;
        }
    }

    private function translateExceptionToAuthenticationResult(OpenAMClientException $ex, $username)
    {
        if ($ex instanceof InvalidPasswordException) {

            $this->logger->info(sprintf('User: %s failed to authenticate due to invalid password', $username));
            $failure = new InvalidCredentialsAuthenticationFailure();

        } elseif ($ex instanceof UserInactiveException || $ex instanceof UserLockedException) {

            $this->logger->info(sprintf('Account locked for user: %s', $username));
            $failure = new AccountLockedAuthenticationFailure();

        } elseif ($ex instanceof TooManyAuthenticationAttemptsException) {

            $opt = $this->openAMOptions;
            $attemptsLeft = $opt->getLoginFailureLockoutCount() - $opt->getWarnUserAfterNFailures();
            $attemptsLeft = $attemptsLeft > 0 ? $attemptsLeft : 0;
            $this->logger->info(sprintf('Lockout warning for user: %s', $username, $attemptsLeft));

            $failure = new LockoutWarningAuthenticationFailure($attemptsLeft);

        } else {

            $this->logger->err(
                sprintf('Generic authentication failure for user: %s, message: %s', $username, $ex->getMessage())
            );

            $failure = new GenericAuthenticationFailure('Error');
        }

        return $failure;
    }

    /**
     * Performs a basic check for the username and password fields before sending an authentication request to OpenAM.
     *
     * Note that we can't use the Username and Password validators available in the common package as these validators
     * may not be in-sync with OpenDJ and reject a perfectly valid username and password.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    private function checkCredentials($username, $password)
    {
        return $username && $password;
    }
}
