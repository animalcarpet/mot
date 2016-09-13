<?php
namespace DvsaMotTest\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use Zend\Session\Container;

/**
 * Class AbstractDvsaMotTestController
 *
 * @package DvsaMotTest\Controller
 */
abstract class AbstractDvsaMotTestController extends AbstractAuthActionController
{
    /**
     * @var \Zend\Session\Container
     */
    protected $motSession;

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

    /**
     * @param $motTestNumber
     * @return MotTestDto | null
     */
    protected function getMotTestFromApi($motTestNumber)
    {
        $apiUrl = MotTestUrlBuilder::motTest($motTestNumber)->toString();
        $result = $this->getRestClient()->get($apiUrl);

        $data = ArrayUtils::tryGet($result, 'data');

        return $data;
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
        $urlBuilder = UrlBuilder::of()->motTest()->routeParam("motTestNumber", $motTestNumber)->motTestShortSummary();
        $apiUrl = $urlBuilder->toString();

        $result = $this->getRestClient()->get($apiUrl);

        $data = ArrayUtils::tryGet($result, 'data');

        return $data;
    }

    /**
     * @param null $motTestNumber
     * @return MotTestDto | null
     */
    public function tryGetMotTestOrAddErrorMessages($motTestNumber = null)
    {
        if ($motTestNumber === null) {
            $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
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
            $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
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
            $motTestNumber = (int)$this->params()->fromRoute('motTestNumber', 0);
        }

        try {
            return $this->getMotTestShortSummaryFromApi($motTestNumber);
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        return null;
    }
}
