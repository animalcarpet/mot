<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\TempPasswordChange;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\AccountClaim;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;

/**
 * Class TempPasswordChangeContext
 */
class TempPasswordChangeContext implements Context
{

    const PASSWORD_VALID = 'Newpassword1';

    /**
     * @var TempPasswordChange
     */
    private $tempPasswordChange;

    /**
     * @var AccountClaim
     */
    private $accountClaim;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @param TempPasswordChange $tempPasswordChange
     * @param AccountClaim $accountClaim
     */
    public function __construct(TempPasswordChange $tempPasswordChange, AccountClaim $accountClaim, TestSupportHelper $testSupportHelper)
    {
        $this->tempPasswordChange = $tempPasswordChange;
        $this->accountClaim       = $accountClaim;
        $this->tempPasswordChange->setTestSupportHelper($testSupportHelper);
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext      = $scope->getEnvironment()->getContext(SessionContext::class);
    }


    /**
     * @When /^I update my password$/
     */
    public function iUpdateMyPassword()
    {
        $response = $this->tempPasswordChange->updatePassword(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            self::PASSWORD_VALID
        );

        $passwordUpdateSuccess = $response->getBody()->getData()['success'];
        if ($passwordUpdateSuccess !== true) {
            throw new Exception('Password update not successful');
        }
    }

    /**
     * @Then /^I will no longer be prompted for password change$/
     */
    public function iWillNoLongerBePromptedForPasswordChange()
    {
        PHPUnit::assertFalse($this->isPasswordChangeRequired(), 'Temporary Password held');
    }


    /**
     * @param bool $assert
     */
    public function myAccountHasBeenFlaggedAsTempPassword()
    {
        $isPasswordChangeRequired = $this->isPasswordChangeRequired();
        if ($isPasswordChangeRequired === false) {
            throw new Exception('Account needs temp password flag set');
        }
    }

    protected function isPasswordChangeRequired()
    {
        $response = $this->accountClaim->getIdentityData($this->sessionContext->getCurrentAccessToken());
        return (bool) $response->getBody()->getData()['user']['isPasswordChangeRequired'];
    }
}
