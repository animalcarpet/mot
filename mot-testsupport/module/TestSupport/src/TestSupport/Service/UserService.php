<?php

namespace TestSupport\Service;

use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Client;
use TestSupport\Helper\NotificationsHelper;
use TestSupport\Service\AccountDataService;
use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;
use Zend\View\Model\JsonModel;

class UserService
{
    /**
     * @var \DvsaCommon\HttpRestJson\Client
     */
    private $restClient;

    /**
     * @var TestSupportAccessTokenManager
     */
    private $tokenManager;

    /**
     * @var AccountDataService
     */
    protected $accountDataService;

    /**
     * @var NotificationsHelper
     */
    private $notificationsHelper;

    public function __construct(
        AccountDataService $accountDataService,
        NotificationsHelper $notificationsHelper,
        Client $restClient,
        TestSupportAccessTokenManager $tokenManager)
    {
        $this->restClient   = $restClient;
        $this->tokenManager = $tokenManager;
        $this->accountDataService = $accountDataService;
        $this->notificationsHelper = $notificationsHelper;
    }

    /**
     * Create a basic user with the data supplied
     *
     * @param array $data
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $resultJson = $this->accountDataService->create($data, Role::USER);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::USER);
        return $resultJson;
    }

    /**
     * Creates mot testing certificate
     * @param $data
     * @return mixed
     * @throws UnauthorisedException
     * @throws \Exception
     */
    public function addQualificationDetails($data)
    {
        $accessToken = $this->tokenManager->getToken($data['userName'], $data['userPassword']);

        $this->restClient->setAccessToken($accessToken);
        try {
            $result = $this->restClient->post(
                sprintf('person/%s/mot-testing-certificate', $data['userId']),
                [
                    "id" => null,
                    "vehicleClassGroupCode" => $data['vehicleClassGroupCode'],
                    "siteNumber" => $data['siteNumber'],
                    "certificateNumber" => $data['certificateNumber'],
                    "dateOfQualification" => $data['dateOfQualification']
                ]
            );

            return $result['data'];
        } catch (UnauthorisedException $e){
            throw new UnauthorisedException('Cannot create certificate: ' . $e->getMessage());
        }
    }

    /**
     * Creates mot testing annual assessment certificate
     * @param $data
     * @return mixed
     * @throws UnauthorisedException
     * @throws \Exception
     */
    public function addAnnualAssessmentCertificate($data)
    {
        $accessToken = $this->tokenManager->getToken($data['userName'], $data['userPassword']);

        $this->restClient->setAccessToken($accessToken);
        try {
            $result = $this->restClient->post(
                sprintf('person/%s/mot-testing-annual-certificate/%s',
                    $data['userId'],
                    $data['vehicleClassGroupCode']),
                    [
                        'id' => null,
                        'score' => $data['score'],
                        'certificateNumber' => $data['certificateNumber'],
                        'examDate' => $data['examDate'],
                    ]
            );

            return $result['data'];
        } catch (UnauthorisedException $e){
            throw new UnauthorisedException('Cannot create annual assessment certificate: ' . $e->getMessage());
        }
    }


}