<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Request;
use Exception;

abstract class AbstractMotTest extends MotApi
{
    const STATUS_PASSED = 'PASSED';
    const STATUS_FAILED = 'FAILED';
    const STATUS_ABORTED = 'ABORTED';
    const STATUS_ABANDONED = 'ABANDONED';

    const PATH_STATUS = 'status';

    /**
     * @var string
     */
    private $lastMotTestNumber;

    /**
     * @param string $token
     * @param string $vehicleId
     * @param string $testClass
     */
    abstract public function startMotTest($token, $vehicleId, $testClass);

    /**
     * @return string
     */
    abstract public function getPath();

    /**
     * @param string $token
     * @param array $params
     * @return \Dvsa\Mot\Behat\Support\Response
     * @throws \Exception
     */
    protected function createMotWithParams($token, array $params)
    {
        $response = $this->sendRequest($token, MotApi::METHOD_POST, $this->getPath(), $params);
        $body = $response->getBody();

        if (isset($body['data'])) {
            $this->lastMotTestNumber = $body['data']['motTestNumber'];
        }
        return $response;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLastMotTestNumber()
    {
        if (null === $this->lastMotTestNumber) {
            throw new \Exception('No MOT test has not been started');
        }
        return $this->lastMotTestNumber;
    }

    /**
     * @param string $token
     * @param string $testNumber
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function passed($token, $testNumber)
    {
        $params = [
            'status' => self::STATUS_PASSED,
            'oneTimePassword' => Authentication::ONE_TIME_PASSWORD
        ];

        return $this->setFinalState($token, $testNumber, $params);
    }

    /**
     * @param string $token
     * @param string $testNumber
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function passedWithoutPin($token, $testNumber)
    {
        $params = [ 'status' => self::STATUS_PASSED ];

        return $this->setFinalState($token, $testNumber, $params);
    }

    /**
     * @param string $token
     * @param string $testNumber
     * @param string $clientIp
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function passedWithIp($token, $testNumber, $clientIp)
    {
        return $this->setFinalState(
            $token,
            $testNumber,
            [
                'status' => self::STATUS_PASSED,
                'clientIp' => $clientIp,
                'oneTimePassword' => Authentication::ONE_TIME_PASSWORD
            ]
        );
    }

    /**
     * @param string $token
     * @param string $testNumber
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function failed($token, $testNumber)
    {
        $params = [
            'status' => self::STATUS_FAILED,
            'oneTimePassword' => Authentication::ONE_TIME_PASSWORD
        ];

        return $this->setFinalState($token, $testNumber, $params);
    }

    /**
     * @param string $token
     * @param int $testNumber
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function abort($token, $testNumber)
    {
        $params = [
            'status' => self::STATUS_ABORTED,
            'reasonForCancelId' => 25,
            'cancelComment' => 'ABORTED TEST'
        ];

        return $this->setFinalState($token, $testNumber, $params);
    }

    public function abortTestByVE($token, $testNumber)
    {
        $params = [
            'reasonForAbort' => 'the test was incorrect',
            'status' => 'ABORTED_VE',
        ];

        return $this->setFinalState($token, $testNumber, $params);
    }

    /**
     * @param string $token
     * @param int $testNumber
     * @param int $cancelReason
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function abandon($token, $testNumber, $cancelReason)
    {
        $params = [
            'status' => ($cancelReason == 7) ? self::STATUS_ABANDONED : self::STATUS_ABORTED,
            'reasonForCancelId' => $cancelReason,
            'cancelComment' => 'ABORTED TEST',
            'oneTimePassword' => Authentication::ONE_TIME_PASSWORD
        ];

        return $this->setFinalState($token, $testNumber, $params);
    }

    /**
     * Makes a call to the API to view the data of the MOT
     * @param string $token
     * @param int $motTestNumber
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getMotData($token, $motTestNumber)
    {
        // This end point is the same for both demo and non-demo tests so we can force the path here
        $path = 'mot-test/'.$motTestNumber;
        return $this->sendRequest($token, MotApi::METHOD_GET, $path);
    }

    /**
     * Makes an API call to set the state of the test
     * @param string $token
     * @param string $testNumber
     * @param array $params
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    private function setFinalState($token, $testNumber, array $params = array())
    {
        return $this->sendRequest($token, MotApi::METHOD_POST, $this->getStatusPath($testNumber), $params);
    }

    /**
     * @param string $testNumber
     * @return string
     */
    private function getStatusPath($testNumber)
    {
        return implode(
            '/',
            [
                'mot-test',
                $testNumber,
                self::PATH_STATUS
            ]
        );
    }
}
