<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Datasource\Random;
use Dvsa\Mot\Behat\Support\Api\AccountClaim;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use TestSupport\Service\AccountService;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_Assert as PHPUnit;

class AccountClaimContext implements Context
{
    private $accountClaim;
    private $userData;


    public function __construct(AccountClaim $accountClaim, UserData $userData)
    {
        $this->accountClaim = $accountClaim;
        $this->userData = $userData;
    }


    /**
     * @Then my account has been claimed
     */
    public function myAccountHasNotYetBeenClaimed()
    {
        PHPUnit::assertFalse($this->isAccountClaimRequired());
    }

    /**
     * @When I claim my Account
     */
    public function iClaimMyAccount()
    {
        $account = [
            PersonParams::EMAIL => 'bo@didley.co',
            PersonParams::SECURITY_QUESTION_ONE_ID => AccountService::SECURITY_QUESTION_ID_FIRST_KISS,
            PersonParams::SECURITY_ANSWER_ONE => 'Barby',
            PersonParams::SECURITY_QUESTION_TWO_ID => AccountService::SECURITY_QUESTION_ID_NAME_OF_DOG,
            PersonParams::SECURITY_ANSWER_TWO=> 'Kingston',
            PersonParams::EMAIL_OPT_OUT => false,
            PersonParams::PASSWORD => Random::password(),
        ];

        $user = $this->userData->getCurrentLoggedUser();
        $this->accountClaim->updateAccountClaim($user->getAccessToken(), $user->getUserId(), $account);
    }

    /**
     * @return bool
     */
    private function isAccountClaimRequired()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $response = $this->accountClaim->getIdentityData($user->getAccessToken());

        return (bool) $response->getBody()->getData()['user']['isAccountClaimRequired'];
    }
}
