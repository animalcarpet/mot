<?php

namespace Site\Form;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use Zend\Stdlib\Parameters;

class VtsSiteAssessmentForm extends AbstractFormModel
{
    const FIELD_SITE_ASSESSMENT_SCORE = 'site-assessment-score';
    const FIELD_USER_IS_NOT_ASSESSOR = 'user-is-not-assessor';
    const FIELD_DVSA_EXAMINERS_USER_ID = 'dvsa-examiners-user-id';
    const FIELD_AE_REPRESENTATIVES_FULL_NAME = 'ae-representatives-full-name';
    const FIELD_AE_REPRESENTATIVES_ROLE = 'ae-representatives-role';
    const FIELD_AE_REPRESENTATIVES_USER_ID = 'ae-representatives-user-id';
    const FIELD_TESTERS_USER_ID = 'testers-user-id';
    const FIELD_DATE_OF_ASSESSMENT = 'date-of-assessment';

    const FIELD_SITE_ASSESSMENT_SCORE_LABEL = 'Site assessment risk score';
    const FIELD_DVSA_EXAMINERS_USER_ID_LABEL = 'DVSA examiner\'s User ID';
    const FIELD_AE_REPRESENTATIVES_FULL_NAME_LABEL = 'AE representative\'s full name';
    const FIELD_AE_REPRESENTATIVES_ROLE_LABEL = 'AE representative\'s role';
    const FIELD_AE_REPRESENTATIVES_USER_ID_LABEL = 'AE representative\'s User ID';
    const FIELD_TESTERS_USER_ID_LABEL = 'Tester\'s User ID';
    const FIELD_DATE_OF_ASSESSMENT_LABEL = 'Date of assessment';

    private $siteAssessmentScore;
    private $userIsNotAssessor;
    private $dvsaExaminersUserId;
    private $dvsaExaminersFullName;
    private $aeRepresentativesFullName;
    private $aeRepresentativesRole;
    private $aeRepresentativesUserId;
    private $testerUserId;
    private $testerFullName;
    private $dateOfAssessment;

    private $dayOfAssessment;
    private $monthOfAssessment;
    private $yearOfAssessment;

    private $formUrl;

    /**
     * @param Parameters $postData
     */
    public function fromPost(Parameters $postData)
    {
        $this->clearEmptyParams($postData);

        $this->setAeRepresentativesFullName($postData->get(self::FIELD_AE_REPRESENTATIVES_FULL_NAME))
            ->setAeRepresentativesRole($postData->get(self::FIELD_AE_REPRESENTATIVES_ROLE))
            ->setAeRepresentativesUserId($postData->get(self::FIELD_AE_REPRESENTATIVES_USER_ID))
            ->setDvsaExaminersUserId($postData->get(self::FIELD_DVSA_EXAMINERS_USER_ID))
            ->setSiteAssessmentScore($postData->get(self::FIELD_SITE_ASSESSMENT_SCORE))
            ->setTesterUserId($postData->get(self::FIELD_TESTERS_USER_ID))
            ->setUserIsNotAssessor(filter_var($postData->get(self::FIELD_USER_IS_NOT_ASSESSOR), FILTER_VALIDATE_BOOLEAN))
            ->setDateOfAssessment($this->extractDate($postData))
        ;
    }

    /**
     * @return EnforcementSiteAssessmentDto
     */
    public function toDto()
    {
        $dto = new EnforcementSiteAssessmentDto();

        $dto->setAeRepresentativesFullName($this->getAeRepresentativesFullName())
            ->setAeRepresentativesRole($this->getAeRepresentativesRole())
            ->setAeRepresentativesUserId($this->getAeRepresentativesUserId())
            ->setDvsaExaminersUserId($this->getDvsaExaminersUserId())
            ->setDvsaExaminersFullName($this->getDvsaExaminersFullName())
            ->setSiteAssessmentScore($this->getSiteAssessmentScore())
            ->setTesterUserId($this->getTesterUserId())
            ->setTesterFullName($this->getTesterFullName())
            ->setDateOfAssessment(DateTimeApiFormat::date($this->getDateOfAssessment()))
            ->setUserIsNotAssessor($this->getUserIsNotAssessor())
        ;

        return $dto;
    }

    /**
     * @param EnforcementSiteAssessmentDto $dto
     */
    public function fromDto(EnforcementSiteAssessmentDto $dto)
    {
        $this
            ->setDvsaExaminersFullName($dto->getDvsaExaminersFullName())
            ->setDvsaExaminersUserId($dto->getDvsaExaminersUserId())
            ->setUserIsNotAssessor($dto->getUserIsNotAssessor())
            ->setTesterUserId($dto->getTesterUserId())
            ->setTesterFullName($dto->getTesterFullName())
            ->setAeRepresentativesFullName($dto->getAeRepresentativesFullName())
            ->setAeRepresentativesUserId($dto->getAeRepresentativesUserId())
            ->setAeRepresentativesRole($dto->getAeRepresentativesRole())
            ->setSiteAssessmentScore($dto->getSiteAssessmentScore())
        ;

        $this->updateDateFields($dto);
    }

    public function addErrorsFromApi($errors)
    {
        $this->addErrors($errors);
    }

    /**
     * @return mixed
     */
    public function getSiteAssessmentScore()
    {
        return $this->siteAssessmentScore;
    }

    /**
     * @param mixed $siteAssessmentScore
     *
     * @return VtsSiteAssessmentForm
     */
    public function setSiteAssessmentScore($siteAssessmentScore)
    {
        $this->siteAssessmentScore = $siteAssessmentScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserIsNotAssessor()
    {
        return $this->userIsNotAssessor;
    }

    /**
     * @param mixed $userIsNotAssessor
     *
     * @return VtsSiteAssessmentForm
     */
    public function setUserIsNotAssessor($userIsNotAssessor)
    {
        $this->userIsNotAssessor = $userIsNotAssessor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDvsaExaminersUserId()
    {
        return $this->dvsaExaminersUserId;
    }

    /**
     * @param mixed $dvsaExaminersUserId
     *
     * @return VtsSiteAssessmentForm
     */
    public function setDvsaExaminersUserId($dvsaExaminersUserId)
    {
        $this->dvsaExaminersUserId = $dvsaExaminersUserId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAeRepresentativesFullName()
    {
        return $this->aeRepresentativesFullName;
    }

    /**
     * @param mixed $aeRepresentativesFullName
     *
     * @return VtsSiteAssessmentForm
     */
    public function setAeRepresentativesFullName($aeRepresentativesFullName)
    {
        $this->aeRepresentativesFullName = $aeRepresentativesFullName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAeRepresentativesRole()
    {
        return $this->aeRepresentativesRole;
    }

    /**
     * @param mixed $aeRepresentativesRole
     *
     * @return VtsSiteAssessmentForm
     */
    public function setAeRepresentativesRole($aeRepresentativesRole)
    {
        $this->aeRepresentativesRole = $aeRepresentativesRole;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAeRepresentativesUserId()
    {
        return $this->aeRepresentativesUserId;
    }

    /**
     * @param mixed $aeRepresentativesUserId
     *
     * @return VtsSiteAssessmentForm
     */
    public function setAeRepresentativesUserId($aeRepresentativesUserId)
    {
        $this->aeRepresentativesUserId = $aeRepresentativesUserId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTesterUserId()
    {
        return $this->testerUserId;
    }

    /**
     * @param mixed $testerUserId
     *
     * @return VtsSiteAssessmentForm
     */
    public function setTesterUserId($testerUserId)
    {
        $this->testerUserId = $testerUserId;

        return $this;
    }

    /**
     * @param $formUrl
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
    public function getFormUrl()
    {
        return $this->formUrl;
    }

    /**
     * @return Date
     */
    public function getDateOfAssessment()
    {
        return $this->dateOfAssessment;
    }

    /**
     * @param mixed $dateOfAssessment
     *
     * @return VtsSiteAssessmentForm
     */
    public function setDateOfAssessment(\DateTime $dateOfAssessment)
    {
        $this->dateOfAssessment = $dateOfAssessment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDvsaExaminersFullName()
    {
        return $this->dvsaExaminersFullName;
    }

    /**
     * @param mixed $dvsaExaminersFullName
     *
     * @return VtsSiteAssessmentForm
     */
    public function setDvsaExaminersFullName($dvsaExaminersFullName)
    {
        $this->dvsaExaminersFullName = $dvsaExaminersFullName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTesterFullName()
    {
        return $this->testerFullName;
    }

    /**
     * @param mixed $testerFullName
     *
     * @return VtsSiteAssessmentForm
     */
    public function setTesterFullName($testerFullName)
    {
        $this->testerFullName = $testerFullName;

        return $this;
    }

    /**
     * @param Parameters $postData
     *
     * @return \DateTime|null
     */
    private function extractDate(Parameters $postData)
    {
        $this->dayOfAssessment = $postData->get('dobDay');
        $this->monthOfAssessment = $postData->get('dobMonth');
        $this->yearOfAssessment = $postData->get('dobYear');

        try {
            $date = DateUtils::toDateFromParts(
                $this->dayOfAssessment,
                $this->monthOfAssessment,
                $this->yearOfAssessment
            );
        } catch (DateException $ex) {
            $date = null;
        }

        return $date;
    }

    /**
     * @return mixed
     */
    public function getDayOfAssessment()
    {
        return $this->dayOfAssessment;
    }

    /**
     * @param mixed $dayOfAssessment
     *
     * @return VtsSiteAssessmentForm
     */
    public function setDayOfAssessment($dayOfAssessment)
    {
        $this->dayOfAssessment = $dayOfAssessment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMonthOfAssessment()
    {
        return $this->monthOfAssessment;
    }

    /**
     * @param mixed $monthOfAssessment
     *
     * @return VtsSiteAssessmentForm
     */
    public function setMonthOfAssessment($monthOfAssessment)
    {
        $this->monthOfAssessment = $monthOfAssessment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getYearOfAssessment()
    {
        return $this->yearOfAssessment;
    }

    /**
     * @param mixed $yearOfAssessment
     *
     * @return VtsSiteAssessmentForm
     */
    public function setYearOfAssessment($yearOfAssessment)
    {
        $this->yearOfAssessment = $yearOfAssessment;

        return $this;
    }

    /**
     * @param EnforcementSiteAssessmentDto $dto
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     */
    private function updateDateFields(EnforcementSiteAssessmentDto $dto)
    {
        $dateOfAssessmentStr = $dto->getDateOfAssessment();
        if (!empty($dateOfAssessmentStr)) {
            $dateOfAssessmentDate = DateUtils::toDate($dateOfAssessmentStr);
            $this
                ->setDateOfAssessment($dateOfAssessmentDate)
                ->setYearOfAssessment($dateOfAssessmentDate->format('Y'))
                ->setMonthOfAssessment($dateOfAssessmentDate->format('m'))
                ->setDayOfAssessment($dateOfAssessmentDate->format('d'))
            ;
        }
    }

    private function getFiledLabelMapping()
    {
        return [
          self::FIELD_SITE_ASSESSMENT_SCORE => self::FIELD_SITE_ASSESSMENT_SCORE_LABEL,
          self::FIELD_DVSA_EXAMINERS_USER_ID => self::FIELD_DVSA_EXAMINERS_USER_ID_LABEL,
          self::FIELD_AE_REPRESENTATIVES_FULL_NAME => self::FIELD_AE_REPRESENTATIVES_FULL_NAME_LABEL,
          self::FIELD_AE_REPRESENTATIVES_ROLE => self::FIELD_AE_REPRESENTATIVES_ROLE_LABEL,
          self::FIELD_AE_REPRESENTATIVES_USER_ID => self::FIELD_AE_REPRESENTATIVES_USER_ID_LABEL,
          self::FIELD_TESTERS_USER_ID => self::FIELD_TESTERS_USER_ID_LABEL,
          self::FIELD_DATE_OF_ASSESSMENT => self::FIELD_DATE_OF_ASSESSMENT_LABEL,
        ];
    }

    public function getFieldLabel($field)
    {
        $fieldLabelMapping = $this->getFiledLabelMapping();

        return isset($fieldLabelMapping) ? $fieldLabelMapping[$field] : null;
    }
}
