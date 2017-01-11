<?php

use MotFitnesse\Util\CredentialsProvider;
use MotFitnesse\Util\TestShared;
use MotFitnesse\Util\UrlBuilder;

class IntegrationOpenInterface
{
    private $vrm;
    private $date;
    private $result;
    private $testerUsername;
    private $testerPassword;

    /** @var  $testSupportHelper TestSupportHelper */
    private $testSupportHelper;

    public function setVrm($value)
    {
        $this->vrm = $value;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function beginTable() {

        $this->testSupportHelper = new TestSupportHelper();
        $this->setupTester();

        $vehicleTestHelper = new VehicleTestHelper(FitMotApiClient::create($this->testerUsername, $this->testerPassword));

        $vehicleTestHelper->generateVehicle($this->vehicleSpecManufacturedPre1960());
    }

    public function found()
    {
        $queryParams = [ 'vrm' => $this->vrm ];
        if ($this->date) {
            $queryParams['date'] = $this->date;
        }

        $url = (new UrlBuilder())->integrationOpenInterface()->queryParams($queryParams)->toString();

        try {
            $this->result = TestShared::get($url, $this->testerUsername, $this->testerPassword);
            return 'YES';
        } catch (Exception $e) {
            $this->result = $e->getMessage();
            return 'NO';
        }
    }

    public function result()
    {
        return $this->result;
    }

    public function setComment()
    {
    }

    private function vehicleSpecManufacturedPre1960() {
        return [
            'registrationNumber'    => 'VIN239',
            'manufactureDate'       => '1059-01-01',
        ];
    }

    private function setupTester()
    {
        $schememgt = $this->testSupportHelper->createSchemeManager();
        $tester = $this->testSupportHelper->createTester($schememgt['username'], [1]);
        $this->testerUsername = $tester['username'];
        $this->testerPassword = $tester['password'];
    }
}
