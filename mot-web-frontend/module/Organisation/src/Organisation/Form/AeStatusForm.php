<?php

namespace Organisation\Form;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\Dto\Common\AuthForAeStatusDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use Zend\Stdlib\Parameters;

class AeStatusForm extends AbstractFormModel
{
    const FIELD_STATUS = 'status';
    const FIELD_AO_NUMBER = 'assignedAreaOffice';

    /**
     * @var string
     */
    private $formUrl;

    /**
     * @var string
     */
    private $status;

    /** @var int */
    private $assignedAreaOffice;

    /** @var string */
    private $assignedAreaOfficeLabel;

    /** @var \DvsaEntities\Entity\Site[] */
    private $areaOfficeOptions;

    public function fromPost(Parameters $data)
    {
        $this->setStatus($data->get(self::FIELD_STATUS));
        $this->setAssignedAreaOffice($data->get(self::FIELD_AO_NUMBER));

        return $this;
    }

    public function toDto()
    {
        // Set the status
        $status = (new AuthForAeStatusDto())
            ->setCode($this->getStatus());

        // Set the authorisation
        /** @var AuthorisedExaminerAuthorisationDto $auth */
        $auth = new AuthorisedExaminerAuthorisationDto();
        $auth->setAssignedAreaOffice($this->getAssignedAreaOffice());

        $auth->setStatus($status)
            ->setAssignedAreaOffice($this->assignedAreaOffice);

        // Attached it to the organisation
        $dto = (new OrganisationDto())
            ->setAuthorisedExaminerAuthorisation($auth);

        return $dto;
    }

    public function addErrorsFromApi($errors)
    {
        $this->addErrors($errors);
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->formUrl;
    }

    /**
     * @param string $formUrl
     *
     * @return $this
     */
    public function setFormUrl($formUrl)
    {
        $this->formUrl = $formUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getAssignedAreaOffice()
    {
        return $this->assignedAreaOffice;
    }

//    /**
//     * @return string
//     */
//    public function getAssignedAreaOfficeLabel()
//    {
//        return $this->assignedAreaOfficeLabel;
//    }

//    public function setAssignedAreaOfficeLabel($assignedAreaOfficeLabel)
//    {
//        $this->assignedAreaOfficeLabel = $assignedAreaOfficeLabel;
//    }

    /**
     * @param int $assignedAreaOffice
     */
    public function setAssignedAreaOffice($assignedAreaOffice)
    {
        $this->assignedAreaOffice = $assignedAreaOffice;
    }

    public function setAreaOfficeOptions($areaOfficeList)
    {
        $this->areaOfficeOptions = $areaOfficeList;
    }
    public function getAreaOfficeOptions()
    {
        return $this->areaOfficeOptions;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return [
            AuthorisationForAuthorisedExaminerStatusCode::APPLIED => 'Applied',
            AuthorisationForAuthorisedExaminerStatusCode::APPROVED => 'Approved',
            AuthorisationForAuthorisedExaminerStatusCode::LAPSED => 'Lapsed',
            AuthorisationForAuthorisedExaminerStatusCode::REJECTED => 'Rejected',
            AuthorisationForAuthorisedExaminerStatusCode::RETRACTED => 'Retracted',
            AuthorisationForAuthorisedExaminerStatusCode::SURRENDERED => 'Surrendered',
            AuthorisationForAuthorisedExaminerStatusCode::WITHDRAWN => 'Withdrawn',
        ];
    }
}
