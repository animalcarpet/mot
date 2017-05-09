<?php

namespace SiteApi\Service\Validator;

use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Date\DateUtils;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;

class EnforcementSiteAssessmentValidator extends AbstractValidator
{
    const ERR_NO_SITE_ID = 'No valid site ID was provided';
    const ERR_NO_PERSON_ID = 'No valid user ID was provided';
    const ERR_SITE_NOT_FOUND = 'No site found for given id';
    const ERR_NO_TESTER_PERSON_ID = 'No valid user id was provided';
    const ERR_NO_USER_FOUND = 'User not found';
    const ERR_NO_REPRESENTATIVE_FULL_NAME = "An AE representative's full name must be provided";
    const ERR_NO_EXAMINER_PERSON_ID = 'No valid user id for examiner was provided';
    const ERR_NO_AE_REPRESENTATIVE_ROLE = "An AE representative's role must be provided";
    const AE_REPRESENTATIVE_NOT_FOUND = 'An AE representative must be provided';
    const AE_REPRESENTATIVE_NAME_NOT_FOUND = 'AE representative must be provided';
    const SITE_SCORE_INVALID = 'Site score must be between %.2F and %.2F';
    const ERR_NOT_VALID_DATE = 'Not a valid date';
    const ERR_FUTURE_DATE = 'Future date of assessment is not allowed';

    const FIELD_SITE_ASSESSMENT_SCORE = 'site-assessment-score';
    const FIELD_USER_IS_ASSESSOR = 'user-is-assessor';
    const FIELD_DVSA_EXAMINERS_USER_ID = 'dvsa-examiners-user-id';
    const FIELD_AE_REPRESENTATIVES_FULL_NAME = 'ae-representatives-full-name';
    const FIELD_AE_REPRESENTATIVES_ROLE = 'ae-representatives-role';
    const FIELD_AE_REPRESENTATIVES_USER_ID = 'ae-representatives-user-id';
    const FIELD_TESTERS_USER_ID = 'testers-user-id';
    const FIELD_DATE_OF_ASSESSMENT = 'date-of-assessment';
    const FIELD_SITE_ID = 'siteId';

    private $minSiteScore;
    private $maxSiteScore;
    /** @var EntityManager */
    private $em;

    /**
     * @param MotConfig $config
     * @param $entityManager
     */
    public function __construct(MotConfig $config, $entityManager)
    {
        $this->minSiteScore = $config->get('site_assessment', 'green', 'start');
        $this->maxSiteScore = $config->get('site_assessment', 'red', 'end');
        $this->em = $entityManager;

        parent::__construct();
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validate(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        $this->validateSiteId($siteRiskAssessmentDto);
        $this->validateTesterId($siteRiskAssessmentDto);
        $this->validateDvsaExaminerId($siteRiskAssessmentDto);
        $this->validateAeRepresentativeData($siteRiskAssessmentDto);
        $this->validateAssessmentDate($siteRiskAssessmentDto);
        $this->validateRiskAssessmentScore($siteRiskAssessmentDto);

        $this->errors->throwIfAnyField();
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     */
    private function validateRiskAssessmentScore(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        $score = $siteRiskAssessmentDto->getSiteAssessmentScore();

        if (
            !is_numeric($score) ||
            $score < $this->minSiteScore ||
            $score > $this->maxSiteScore
        ) {
            $this->errors->add(
                sprintf(
                    self::SITE_SCORE_INVALID,
                    $this->minSiteScore,
                    $this->maxSiteScore
                ),
                self::FIELD_SITE_ASSESSMENT_SCORE
            );
        }
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     */
    private function validateAssessmentDate(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        try {
            $date = DateUtils::toDate($siteRiskAssessmentDto->getDateOfAssessment());
            $dateNow = DateUtils::nowAsUserDateTime();
            if ($date > $dateNow) {
                $this->errors->add(self::ERR_FUTURE_DATE, self::FIELD_DATE_OF_ASSESSMENT);
            }
        } catch (\Exception $ex) {
            $this->errors->add(self::ERR_NOT_VALID_DATE, self::FIELD_DATE_OF_ASSESSMENT);
        }
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     */
    private function validateSiteId(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        if (!is_numeric($siteRiskAssessmentDto->getSiteId())) {
            $this->errors->add(self::ERR_NO_SITE_ID, self::FIELD_SITE_ID);
        } elseif (!$this->isSiteExists($siteRiskAssessmentDto->getSiteId())) {
            $this->errors->add(self::ERR_SITE_NOT_FOUND);
        }
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     */
    private function validateTesterId(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        if (empty($siteRiskAssessmentDto->getTesterUserId())) {
            $this->errors->add(self::ERR_NO_PERSON_ID, self::FIELD_TESTERS_USER_ID);
        } elseif (!$this->isPersonExists($siteRiskAssessmentDto->getTesterUserId())) {
            $this->errors->add(self::ERR_NO_USER_FOUND, self::FIELD_TESTERS_USER_ID);
        }
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     */
    private function validateDvsaExaminerId(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        //don't check field if examiner is the user logged in
        if ($siteRiskAssessmentDto->getUserIsNotAssessor() === false) {
            return;
        }

        if (empty($siteRiskAssessmentDto->getDvsaExaminersUserId())) {
            $this->errors->add(self::ERR_NO_EXAMINER_PERSON_ID, self::FIELD_DVSA_EXAMINERS_USER_ID);
        } elseif (!$this->isPersonExists($siteRiskAssessmentDto->getDvsaExaminersUserId())) {
            $this->errors->add(self::ERR_NO_USER_FOUND, self::FIELD_DVSA_EXAMINERS_USER_ID);
        }
    }

    /**
     * @param EnforcementSiteAssessmentDto $siteRiskAssessmentDto
     */
    private function validateAeRepresentativeData(EnforcementSiteAssessmentDto $siteRiskAssessmentDto)
    {
        // providing AE role is mandatory cause we don't do lookup for appropriate role in system
        //just persist the user typed value (@see Alasdair Cameron)
        if (empty($siteRiskAssessmentDto->getAeRepresentativesRole())) {
            $this->errors->add(self::ERR_NO_AE_REPRESENTATIVE_ROLE, self::FIELD_AE_REPRESENTATIVES_ROLE);
        }

        if (empty($siteRiskAssessmentDto->getAeRepresentativesUserId())) {
            if (empty($siteRiskAssessmentDto->getAeRepresentativesFullName())) {
                $this->errors->add(self::ERR_NO_REPRESENTATIVE_FULL_NAME, self::FIELD_AE_REPRESENTATIVES_FULL_NAME);
            }
        } else {
            if (!$this->isPersonExists($siteRiskAssessmentDto->getAeRepresentativesUserId())) {
                $this->errors->add(self::ERR_NO_USER_FOUND, self::FIELD_AE_REPRESENTATIVES_USER_ID);
            }
        }
    }

    /**
     * @param $siteId
     *
     * @return bool
     */
    private function isSiteExists($siteId)
    {
        return $this->getSiteEntityById($siteId) instanceof Site;
    }

    /**
     * @param $siteId
     *
     * @return null|Site
     */
    private function getSiteEntityById($siteId)
    {
        return $this->em->getRepository(Site::class)->findOneBy(['id' => $siteId]);
    }

    /**
     * @param $username
     *
     * @return bool
     */
    private function isPersonExists($username)
    {
        return $this->getPersonEntityByUsername($username) instanceof Person;
    }

    /**
     * @param $username
     *
     * @return null|Person
     */
    private function getPersonEntityByUsername($username)
    {
        return $this->em->getRepository(Person::class)->findOneBy(['username' => $username]);
    }
}
