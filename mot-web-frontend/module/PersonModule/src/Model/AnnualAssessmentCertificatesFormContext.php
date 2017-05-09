<?php
/**
 * Created by PhpStorm.
 * User: szymonf
 * Date: 24.03.2016
 * Time: 11:33.
 */

namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\Frontend\PersonModule\Controller\QualificationDetailsController;
use Zend\Mvc\Controller\AbstractActionController;

class AnnualAssessmentCertificatesFormContext implements FormContextInterface
{
    private $targetPersonId;
    private $group;
    private $controller;
    private $loggedInPersonId;
    private $certificateId;

    public function __construct($targetPersonId, $loggedInPersonId, $group, $certificateId, $controller)
    {
        $this->targetPersonId = $targetPersonId;
        $this->group = $group;
        $this->controller = $controller;
        $this->loggedInPersonId = $loggedInPersonId;
        $this->certificateId = $certificateId;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getTargetPersonId()
    {
        return $this->targetPersonId;
    }

    /**
     * @param int $targetPersonId
     */
    public function setTargetPersonId($targetPersonId)
    {
        $this->targetPersonId = $targetPersonId;
    }

    /**
     * @return QualificationDetailsController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param AbstractActionController $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return int
     */
    public function getLoggedInPersonId()
    {
        return $this->loggedInPersonId;
    }

    /**
     * @param int $loggedInPersonId
     */
    public function setLoggedInPersonId($loggedInPersonId)
    {
        $this->loggedInPersonId = $loggedInPersonId;
    }

    /**
     * @return int
     */
    public function getCertificateId()
    {
        return $this->certificateId;
    }

    /**
     * @param int $certificateId
     */
    public function setCertificateId($certificateId)
    {
        $this->certificateId = $certificateId;
    }
}
