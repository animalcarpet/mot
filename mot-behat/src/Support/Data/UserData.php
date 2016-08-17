<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\HttpClient\ReflectiveClient;
use DvsaCommon\Utility\ArrayUtils;

class UserData
{
    const DEFAULT_PASSWORD = "123456";

    private $session;
    private $testSupportHelper;
    private $client;

    /** @var AuthenticatedUser */
    private $currentLoggedUser;
    private $userCollection;

    public function __construct(
        Session $session,
        TestSupportHelper $testSupportHelper,
        ReflectiveClient $client
    )
    {
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
        $this->userCollection = SharedDataCollection::get(AuthenticatedUser::class);
        $this->client = $client;
    }

    /**
     * @param array $data
     * @return AuthenticatedUser
     */
    public function createAreaOffice1User(array $data = [], $name = "areaOffice1User")
    {
        if (array_key_exists("username", $data) === true) {
            $name = $data["username"];
        }

        if ($this->userCollection->containsKey($name)) {
            return $this->userCollection->get($name);
        }

        $service = $this->testSupportHelper->getAreaOffice1Service();
        $user = $service->create($data);

        $authenticatedUser = $this->session->startSession(
            $user->data["username"],
            $user->data["password"]
        );

        $this->userCollection->add($authenticatedUser, $name);

        return $authenticatedUser;
    }

    public function createTester(array $data = [], $name = null)
    {
        if ($name === null && array_key_exists("username", $data) === true) {
            $name = $data["username"];
        }

        $user = $this->tryGet($name);
        if ($user !== null) {
            return $user;
        }

        $service = $this->testSupportHelper->getTesterService();
        $user = $service->create($data);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createAedm(array $data = [])
    {
        $aeIds = ArrayUtils::get($data, "aeIds");

        if (array_key_exists("username", $data) === true) {
            $name = $data["username"];
        } else {
            $name = "aedm_" . join("_", $aeIds);
        }

        $user = $this->tryGet($name);
        if ($user !== null) {
            return $user;
        }


        $aedm = $this->testSupportHelper->getAedmService();
        $user = $aedm->create(["aeIds" => $aeIds]);

        return $this->createAuthenticatedUser($user, $name);
    }

    /**
     * @param $userName
     * @return AuthenticatedUser
     */
    public function get($userName)
    {
        if ($this->userCollection->containsKey($userName)) {
            return $this->userCollection->get($userName);
        }

        $users = $this->userCollection->filter(function (AuthenticatedUser $user) use ($userName) {
            return $user->getUsername() === $userName;
        });

        if (count($users) === 1) {
            return $users->first();
        }

        throw new \InvalidArgumentException(sprintf("User with username '%s' not found", $userName));
    }

    public function getAedmByAeId($aeId)
    {
        return $this->get("aedm_" . $aeId);
    }

    public function tryGet($username)
    {
        try {
            return $this->get($username);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function setCurrentLoggedUser(AuthenticatedUser $user)
    {
        $this->currentLoggedUser = $user;
        $this->client->setAccessToken($user->getAccessToken());
    }

    public function getCurrentLoggedUser()
    {
        return $this->currentLoggedUser;
    }

    private function createAuthenticatedUser($user, $name = null)
    {
        $authenticatedUser = $this->session->startSession(
            $user->data["username"],
            $user->data["password"]
        );

        if ($name === null) {
            $name = $authenticatedUser->getUsername();
        }

        $this->userCollection->add($authenticatedUser, $name);

        return $authenticatedUser;
    }
}
