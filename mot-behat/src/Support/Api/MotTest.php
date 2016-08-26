<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\HttpClient;
use Dvsa\Mot\Behat\Support\Request;

class MotTest extends AbstractMotTest
{
    const PATH = 'mot-test';
    const PATH_GET_CERT = 'mot-test-certificate?number=651157444199';
    const PATH_SEARCH = 'mot-test-search';
    const PATH_SURVEY = '/survey';
    const PATH_SURVEY_REPORTS = '/reports';

    /**
     * @var Person
     */
    private $person;

    public function __construct(HttpClient $client, Person $person)
    {
        parent::__construct($client);

        $this->person = $person;
    }

    public function getPath()
    {
        return self::PATH;
    }

    /**
     * @param string $token
     * @param string $vehicleId
     * @param string $testClass
     *
     * @param array $params
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function startMOTTest($token, $vehicleId = '3', $testClass = '4', $params = [])
    {
        $defaults = [
            'vehicleId' => $vehicleId,
            'vehicleTestingStationId' => '1',
            'primaryColour' => 'L',
            'secondaryColour' => 'L',
            'fuelTypeId' => 'PE',
            'vehicleClassCode' => $testClass,
            'hasRegistration' => '1',
            'oneTimePassword' => Authentication::ONE_TIME_PASSWORD,
        ];

        $params = array_replace($defaults, $params);

        return parent::createMotWithParams($token, $params);
    }

    public function startMOTTestForTester($token, $userId)
    {
        if (!$this->isMOTTestInProgressForTester($token, $userId)) {
            return $this->startMOTTest($token);
        } else {
            $currentMotTestNumber = $this->getInProgressTestId($token, $userId);
            $this->abort($token, $currentMotTestNumber);

            return $this->startMOTTest($token);
        }
    }

    public function startNewMotTestWithVehicleId($token, $userId, $vehicleId, $vehicleClass = '4', $motTestParams = [])
    {
        if (!$this->isMOTTestInProgressForTester($token, $userId)) {
            return $this->startMOTTest($token, $vehicleId, $vehicleClass, $motTestParams);
        } else {
            //Stop Current Test and Start a New one with the new Vehicle Id

            $currentMotTestNumber = $this->getInProgressTestId($token, $userId);
            $this->abort($token, $currentMotTestNumber);

            return $this->startMOTTest($token, $vehicleId, $vehicleClass);
        }
    }

    public function isMOTTestInProgressForTester($token, $user_id)
    {
        $response = $this->person->getPersonDashboard($token, $user_id);

        return $response->getBody()['data']['inProgressTestNumber'] == null ? false : true;
    }

    public function getInProgressTestId($token, $user_id)
    {
        $response = $this->person->getPersonDashboard($token, $user_id);

        return $response->getBody()['data']['inProgressTestNumber'];
    }

    public function getMotData($token, $motTestNumber)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH . '/' . $motTestNumber
        );
    }

    public function getMOTCertificateDetails($token)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH_GET_CERT
        );
    }

    public function searchMOTTest($token, $params = [])
    {
        $defaults = [
            'status' => [],
            'testType' => [],
            'format' => "DATA_TABLES",
            'pageNr' => 0,
            'rowsCount' => 25000,
            'sortBy' => 3, //sort by started date, id assigned in MotTestSearchParam class
            'sortDirection' => "DESC",
            '_class' => 'DvsaCommon\Dto\Search\MotTestSearchParamsDto',
        ];

        $params = array_replace($defaults, $params);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::PATH_SEARCH,
            $params
        );
    }

    public function searchMotTestHistory($token, array $params)
    {
        $defaults = [
            "tester" => 0,
            "dateFrom" => (new \DateTime())->sub(new \DateInterval('P30D'))->getTimestamp(),
            "dateTo" => (new \DateTime())->getTimestamp(),
            "format" => "DATA_TABLES",
            "rowCount" => 10,
            "start" => 0,
            "sortColumnId" => 0,
            "sortDirection" => "desc",
            "pageNumber" => 1
        ];

        $params = array_replace($defaults, $params);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH_SEARCH . "?" . http_build_query($params)
        );
    }

    public function submitSurveyResponse($token, $motTestId, $satisfactionRating)
    {
        $body = json_encode(
            ['mot_test_number' => $motTestId, 'satisfaction_rating' => $satisfactionRating]
        );

        return $this->client->request(new Request(
            MotApi::METHOD_POST,
            self::PATH_SURVEY,
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '. $token],
            $body
        ));
    }

    /**
     * @param $token
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function generateSurveyReports($token)
    {
        return $this->client->request(new Request(
            MotApi::METHOD_GET,
            self::PATH_SURVEY.self::PATH_SURVEY_REPORTS.'/generate',
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '. $token]
        ));
    }
    
    public function getSurveyReports($token)
    {
        return $this->client->request(new Request(
            MotApi::METHOD_GET,
            self::PATH_SURVEY.self::PATH_SURVEY_REPORTS,
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '. $token]
        ));
    }
}
