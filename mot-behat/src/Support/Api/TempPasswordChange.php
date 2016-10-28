<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Request;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;

class TempPasswordChange extends MotApi
{
    const PATH_PASSWORD_UPDATE = 'account/password-update/{user_id}';

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @param TestSupportHelper $testSupportHelper
     */
    public function setTestSupportHelper(TestSupportHelper $testSupportHelper)
    {
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @param string $token
     * @param int $userId
     * @param string $password
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function updatePassword($token, $userId, $password)
    {
        return $this->sendPutRequest(
            $token,
            str_replace('{user_id}', $userId, self::PATH_PASSWORD_UPDATE),
            [ PersonParams::PASSWORD => $this->testSupportHelper->getParamObfuscatorService()->obfuscate($password) ]
        );
    }
}
