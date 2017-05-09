<?php

namespace Application\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\TesterUrlBuilder;
use Zend\Session\Container;

/**
 * Class LoggedInUserManager.
 */
class LoggedInUserManager
{
    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /**
     * @var Container
     */
    private $motSession;

    /**
     * @var Client
     */
    private $restClient;

    /**
     * For use by test code only.
     */
    public function setIdentityProvider(MotIdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * @var array
     *            cached PHP array of the result of the tester data API call
     */
    private $testerData;

    /** @var MotFrontendAuthorisationServiceInterface $motFrontendAuthorizationService */
    private $motFrontendAuthorizationService;

    /**
     * @param MotIdentityProviderInterface $identityProvider
     * @param Container                    $motSession
     * @param Client                       $restClient
     */
    public function __construct(
        MotIdentityProviderInterface $identityProvider,
        MotFrontendAuthorisationServiceInterface $motFrontendAuthorizationService,
        Container $motSession,
        $restClient
    ) {
        $this->identityProvider = $identityProvider;
        $this->motFrontendAuthorizationService = $motFrontendAuthorizationService;
        $this->motSession = $motSession;
        $this->restClient = $restClient;
    }

    /**
     * @return Client
     */
    private function getAuthRestClient()
    {
        return $this->restClient;
    }

    public function getTesterData()
    {
        if (null == $this->testerData) {
            $this->testerData = $this->getUsersTestingDataFromApi();
        }

        return $this->testerData;
    }

    private function getUsersTestingDataFromApi()
    {
        $apiUrl = TesterUrlBuilder::create()->routeParam('id', $this->getIdentity()->getUserId());
        $result = $this->getAuthRestClient()->get($apiUrl);

        return $result['data'];
    }

    public function discoverCurrentLocation(VehicleTestingStation $site = null)
    {
        if ($this->motFrontendAuthorizationService->isTester()) {
            $tester = $this->getTesterData();
            if (!empty($tester['testInProgress'])) {
                $this->changeCurrentLocation($tester['testInProgress']['vts']['id']);
            } elseif ($site !== null) {
                $this->changeCurrentLocation($site->getVtsId());
            }
        }
    }

    /**
     * @return array
     */
    public function getAllVts()
    {
        $testerId = $this->getIdentity()->getUserId();
        $apiUrl = TesterUrlBuilder::create()->vehicleTestingStations($testerId);
        $result = $this->getAuthRestClient()->get($apiUrl);

        return $result['data'];
    }

    /**
     * @return array
     */
    public function getAllVtsWithSlotBalance()
    {
        $testerId = $this->getIdentity()->getUserId();
        $apiUrl = TesterUrlBuilder::create()->vehicleTestingStationWithSlotBalance($testerId);
        $result = $this->getAuthRestClient()->get($apiUrl);

        return $result['data'];
    }

    /**
     * @param int $vtsId
     */
    public function changeCurrentLocation($vtsId)
    {
        $userTestingData = $this->getAllVtsWithSlotBalance();
        $currentVts = $this->findVtsInList($vtsId, $userTestingData['vtsSites']);
        $this->getIdentity()->setCurrentVts($currentVts);
    }

    public function clearCurrentLocation()
    {
        $this->getIdentity()->clearCurrentVts();
    }

    /**
     * @param $vtsId
     * @param $vtsList
     *
     * @return VehicleTestingStation
     */
    private function findVtsInList($vtsId, $vtsList)
    {
        foreach ($vtsList as $vts) {
            if ($vts['id'] === $vtsId) {
                return new VehicleTestingStation($vts);
            }
        }
        throw new \InvalidArgumentException("Could not find VTS of id $vtsId on tester's VTS list");
    }

    /**
     * TODO Change to MotIdentityProviderInterface, but also move the place the current VTS is stored into this class
     * at the same time.
     *
     * @return Identity
     */
    private function getIdentity()
    {
        return $this->identityProvider->getIdentity();
    }
}
