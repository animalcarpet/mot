<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Request;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Exception;

class Session extends MotApi
{
    const PATH = 'session';

    const PATH_CONFIRMATION = 'session/confirmation';

    /**
     * @param string $username
     * @param string $password
     *
     * @return AuthenticatedUser
     * @throws Exception
     */
    public function startSession($username, $password)
    {
        $response = $this->createNewSession($username, $password);

        $data = $response->getBody()->getData();
        if (!isset($data[PersonParams::ACCESS_TOKEN])) {
            throw new Exception(sprintf('No access Token returned with User Credentials: %s / %s', $username, $password));
        }

        return new AuthenticatedUser(
            $response->getBody()['data']['user'][PersonParams::USER_ID],
            $response->getBody()['data']['user'][PersonParams::USERNAME],
            $response->getBody()['data'][PersonParams::ACCESS_TOKEN]
        );
    }

    /**
     * @param string $username
     * @param string $password
     * @return Response
     */
    private function createNewSession($username, $password)
    {
        $params = [
            PersonParams::USERNAME => $username,
            PersonParams::PASSWORD => $password,
        ];

        return $this->sendPostRequest(
            null,
            self::PATH,
            $params
        );
    }

    /**
     * @param string $accessToken
     * @param string $password
     * @return Response
     */
    public function confirmSession($accessToken, $password)
    {
        return $this->sendPostRequest(
            $accessToken,
            self::PATH_CONFIRMATION,
            [PersonParams::PASSWORD => $password]
        );
    }

    /**
     * High level function to allow logging in as a tester
     * @param TestSupportHelper $helper
     * @param array $siteIds
     * @return string Returned token
     * @throws Exception
     */
    public function logInAsTester(TestSupportHelper $helper, array $siteIds)
    {
        $testerService = $helper->getTesterService();
        $tester = $testerService->create([
            PersonParams::SITE_IDS => $siteIds,
        ]);

        return $this->startSession($tester->data[PersonParams::USERNAME], $tester->data[PersonParams::PASSWORD]);
    }

    public function logInAsNewUser(TestSupportHelper $helper)
    {
        $user = $this->createNewUser($helper);

        return $this->startSession($user->data[PersonParams::USERNAME], $user->data[PersonParams::PASSWORD]);
    }

    public function createNewUser(TestSupportHelper $helper)
    {
        $service = $helper->getUserService();
        return $service->create([]);
    }
}
