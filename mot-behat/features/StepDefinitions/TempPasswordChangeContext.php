<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\TempPasswordChange;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\AccountClaim;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;

/**
 * Class TempPasswordChangeContext
 */
class TempPasswordChangeContext implements Context
{
    const PASSWORD_VALID = 'Newpassword1';

    private $tempPasswordChange;
    private $accountClaim;
    private $userData;

    public function __construct(
        TempPasswordChange $tempPasswordChange,
        AccountClaim $accountClaim,
        TestSupportHelper $testSupportHelper,
        UserData $userData
    )
    {
        $this->tempPasswordChange = $tempPasswordChange;
        $this->accountClaim       = $accountClaim;
        $this->userData       = $userData;
        $this->tempPasswordChange->setTestSupportHelper($testSupportHelper);
    }

    /**
     * @When /^I update my password$/
     */
    public function iUpdateMyPassword()
    {
        $response = $this->tempPasswordChange->updatePassword(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId(),
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
        $response = $this->accountClaim->getIdentityData($this->userData->getCurrentLoggedUser()->getAccessToken());
        return (bool) $response->getBody()->getData()['user']['isPasswordChangeRequired'];
    }
}
