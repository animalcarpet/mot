<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultAuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultVehicleTestingStation;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\HttpClient\ReflectiveClient;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Service\AccountService;

class UserData
{
    const DEFAULT_TESTER_NAME = "tester";

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

    public function createTester($name = self::DEFAULT_TESTER_NAME)
    {
        $site = DefaultVehicleTestingStation::get();
        return $this->createTesterAssignedWitSite($site->getId(),$name);
    }

    public function createTesterAssignedWitSite($siteId, $name = self::DEFAULT_TESTER_NAME)
    {
        return $this->createTesterAssignedWithManySites([$siteId], $name);
    }

    public function createTesterAssignedWithManySites(array $sitesId, $name = self::DEFAULT_TESTER_NAME)
    {
        return $this->createTesterWithParams([PersonParams::SITE_IDS => $sitesId], $name);
    }

    public function createTesterWithParams(array $params, $name = self::DEFAULT_TESTER_NAME)
    {
        if ($name === null && array_key_exists(PersonParams::USERNAME, $params) === true) {
            $name = $params[PersonParams::USERNAME];
        }

        $user = $this->tryGet($name);
        if ($user !== null) {
            return $user;
        }

        $default = PersonParams::getDefaultParams();
        $params = array_replace($default, $params);

        $service = $this->testSupportHelper->getTesterService();
        $user = $service->create($params);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createAreaOffice1User($name = "areaOffice1User")
    {
        return $this->createAreaOffice1UserWithParams([], $name);
    }

    public function createAreaOffice1UserWithParams(array $params, $name = "areaOffice1User")
    {
        if (array_key_exists(PersonParams::USERNAME, $params) === true) {
            $name = $params[PersonParams::USERNAME];
        }

        if ($this->userCollection->containsKey($name)) {
            return $this->userCollection->get($name);
        }

        $default = PersonParams::getDefaultParams();
        $params = array_replace($default, $params);

        $service = $this->testSupportHelper->getAreaOffice1Service();
        $user = $service->create($params);

        $authenticatedUser = $this->session->startSession(
            $user->data[PersonParams::USERNAME],
            $user->data[PersonParams::PASSWORD]
        );

        $this->userCollection->add($authenticatedUser, $name);

        return $authenticatedUser;
    }

    public function createAreaOffice2User($name = "areaOffice2User")
    {
        $service = $this->testSupportHelper->getAreaOffice2Service();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createCronUser($name = "Cron User")
    {
        $service = $this->testSupportHelper->getCronUserService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createFinanceUser($name = "Finance User")
    {
        $service = $this->testSupportHelper->getFinanceUserService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createAedm($name = null)
    {
        $ae = DefaultAuthorisedExaminer::get();
        return $this->createAedmAssignedWithOrganisation($ae->getId(), $name);

    }

    public function createAedmAssignedWithOrganisation($orgId, $name = null)
    {
        return $this->createAedmAssignedWithManyOrganisations([$orgId], $name);
    }

    public function createAedmAssignedWithManyOrganisations(array $orgsId, $name = null)
    {
        return $this->createAedmWithParams([PersonParams::AE_IDS => $orgsId], $name);
    }

    public function createAedmWithParams(array $params, $name = null)
    {
        $aeIds = ArrayUtils::get($params, PersonParams::AE_IDS);

        if ($name === null && array_key_exists(PersonParams::USERNAME, $params) === true) {
            $name = $params[PersonParams::USERNAME];
        } elseif ($name === null) {
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

    public function getAedmByAeId($aeId)
    {
        return $this->get("aedm_" . $aeId);
    }

    public function createVehicleExaminer($name = "Vehicle Examiner")
    {
        $user = $this->tryGet($name);
        if ($user !== null) {
            return $user;
        }

        $service = $this->testSupportHelper->getVehicleExaminerService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createCustomerServiceOperator($name = "Customer Service Operator")
    {
        $user = $this->tryGet($name);
        if ($user !== null) {
            return $user;
        }

        $service = $this->testSupportHelper->getCscoService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createUser($name = "User")
    {
        $service = $this->testSupportHelper->getUserService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createGVTSTester($name = "GVTS Tester")
    {
        $service = $this->testSupportHelper->getGVTSTesterService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createCustomerServiceManager($name = "Customer Service Manager")
    {
        $service = $this->testSupportHelper->getCsmService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createDVLAManager($name = "DVLA Manager")
    {
        $service = $this->testSupportHelper->getDVLAManagerService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createDVLAOperative($name = "DVLA Operative")
    {
        $service = $this->testSupportHelper->getDVLAOperativeService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createSchemeManager($name = "Scheme Manager")
    {
        $service = $this->testSupportHelper->getSchemeManagerService();
        $user = $service->create([]);

        return $this->createAuthenticatedUser($user, $name);
    }

    public function createSiteManager($siteId, $name = "Site Manager")
    {
        $siteManagerService = $this->testSupportHelper->getSiteUserDataService();

        $data = [
            PersonParams::SITE_IDS => [ $siteId ],
            PersonParams::REQUESTOR => [
                PersonParams::USERNAME => "schememgt",
                PersonParams::PASSWORD => AccountService::PASSWORD
            ]
        ];
        $user = $siteManagerService->create($data, RoleCode::SITE_MANAGER);
        return $this->createAuthenticatedUser($user, $name);
    }

    public function createSiteAdmin($siteId, $name = "Site Admin")
    {
        $service = $this->testSupportHelper->getSiteUserDataService();

        $tester = $this->createTesterAssignedWitSite($siteId, $name);
        $data = [
            PersonParams::SITE_IDS => [ $siteId ],
            PersonParams::REQUESTOR => [
                PersonParams::USERNAME => $tester->getUsername(),
                PersonParams::PASSWORD => AccountService::PASSWORD
            ]
        ];
        $user = $service->create($data, RoleCode::SITE_ADMIN);
        return $this->createAuthenticatedUser($user, $name);
    }

    public function createSchemeUser($name = "Scheme User")
    {
        $service = $this->testSupportHelper->getSchemeUserService();
        $user = $service->create([]);

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

    public function tryGet($username)
    {
        try {
            return $this->get($username);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function getAll()
    {
        return $this->userCollection;
    }

    /**
     * @return AuthenticatedUser
     */
    public function getLast()
    {
        return $this->getAll()->last();
    }

    public function setCurrentLoggedUser(AuthenticatedUser $user = null)
    {
        $this->currentLoggedUser = $user;
        if ($user === null) {
            $this->client->setAccessToken(null);
        } else {
            $this->client->setAccessToken($user->getAccessToken());
        }
    }

    public function getCurrentLoggedUser()
    {
        return $this->currentLoggedUser;
    }

    private function createAuthenticatedUser($user, $name = null)
    {
        $authenticatedUser = $this->session->startSession(
            $user->data[PersonParams::USERNAME],
            $user->data[PersonParams::PASSWORD]
        );

        if ($name === null) {
            $name = $authenticatedUser->getUsername();
        }

        $this->userCollection->add($authenticatedUser, $name);

        return $authenticatedUser;
    }
}
