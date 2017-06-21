<?php

namespace DvsaMotTest\Controller;

use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class AbstractDvsaMotTestController.
 */
abstract class AbstractDvsaMotTestController extends AbstractAuthActionController
{
    /**
     * @var \Zend\Session\Container
     */
    protected $motSession;

    /**
     * @var MotTestService
     */
    protected $motTestServiceClient;

    /**
     * @return VehicleService
     */
    protected $vehicleServiceClient;

    /**
     * @return VehicleService
     */
    protected function getVehicleServiceClient()
    {
        if (!$this->vehicleServiceClient) {
            $sm = $this->getServiceLocator();
            $this->vehicleServiceClient = $sm->get(VehicleService::class);
        }

        return $this->vehicleServiceClient;
    }

    /**
     * @return MotTestService
     */
    protected function getMotTestServiceClient()
    {
        if (!$this->motTestServiceClient) {
            $sm = $this->getServiceLocator();
            $this->motTestServiceClient = $sm->get(MotTestService::class);
        }

        return $this->motTestServiceClient;
    }

    protected function getSession()
    {
        if (!$this->motSession) {
            $sm = $this->getServiceLocator();
            $this->motSession = $sm->get('MotSession');
        }

        return $this->motSession;
    }

    /**
     * @param string $testType
     *
     * @return string
     */
    public static function getTestName($testType)
    {
        if ($testType === MotTestTypeCode::RE_TEST) {
            $testName = 'MOT re-test';
        } elseif ($testType === MotTestTypeCode::NON_MOT_TEST) {
            $testName = 'Non-MOT test';
        } elseif (\DvsaCommon\Domain\MotTestType::isReinspection($testType)) {
            $testName = 'MOT reinspection';
        } else {
            $testName = 'MOT test';
        }

        return $testName;
    }

    /**
     * @param string $status
     *
     * @return string
     */
    public static function getTestStatusName($status)
    {
        if ($status === MotTestStatusName::ACTIVE) {
            $name = 'In progress';
        } elseif ($status == MotTestStatusName::PASSED) {
            $name = 'Pass';
        } elseif ($status == MotTestStatusName::FAILED) {
            $name = 'Fail';
        } elseif ($status === MotTestStatusName::ABANDONED) {
            $name = 'Abandoned';
        } elseif ($status === MotTestStatusName::ABORTED) {
            $name = 'Aborted';
        } elseif ($status === MotTestStatusName::ABORTED_VE) {
            $name = 'Aborted';
        } else {
            $name = 'undefined';
        }

        return $name;
    }

    protected function getMotTestFromApi($motTestNumber)
    {
        return $this->getMotTestServiceClient()->getMotTestByTestNumber((string) $motTestNumber);
    }

    protected function getMotTestStatusFromApi($motTestNumber)
    {
        $apiUrl = MotTestUrlBuilder::motTestStatus($motTestNumber)->toString();
        $result = $this->getRestClient()->get($apiUrl);

        $data = ArrayUtils::tryGet($result, 'data');

        return $data;
    }

    protected function getMinimalMotTestFromApi($motTestNumber)
    {
        $apiUrl = MotTestUrlBuilder::minimal($motTestNumber);
        $result = $this->getRestClient()->get($apiUrl->toString());

        $data = ArrayUtils::tryGet($result, 'data');

        return $data;
    }

    protected function getMotTestShortSummaryFromApi($motTestNumber)
    {
        $urlBuilder = UrlBuilder::of()->motTest()->routeParam('motTestNumber', $motTestNumber)->motTestShortSummary();
        $apiUrl = $urlBuilder->toString();

        $result = $this->getRestClient()->get($apiUrl);

        $data = ArrayUtils::tryGet($result, 'data');

        return $data;
    }

    /**
     * @param null $motTestNumber
     *
     * @return MotTest | null
     */
    public function tryGetMotTestOrAddErrorMessages($motTestNumber = null)
    {
        if ($motTestNumber === null) {
            $motTestNumber = (int) $this->params()->fromRoute('motTestNumber', 0);
        }

        try {
            return $this->getMotTestFromApi($motTestNumber);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    public function tryGetMotTestStatusOrAddErrorMessages($motTestNumber = null)
    {
        if ($motTestNumber === null) {
            $motTestNumber = (int) $this->params()->fromRoute('motTestNumber', 0);
        }

        try {
            return $this->getMotTestStatusFromApi($motTestNumber);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }

    public function tryGetMotTestShortSummaryOrAddErrorMessages($motTestNumber = null)
    {
        if ($motTestNumber === null) {
            $motTestNumber = (int) $this->params()->fromRoute('motTestNumber', 0);
        }

        try {
            return $this->getMotTestShortSummaryFromApi($motTestNumber);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }
}
