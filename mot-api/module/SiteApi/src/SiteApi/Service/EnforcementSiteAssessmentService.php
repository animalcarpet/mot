<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Site;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use DvsaEntities\Entity\Person;

class EnforcementSiteAssessmentService
{
    /** @var EntityManager */
    private $em;
    /** @var $validator */
    private $validator;
    /** @var $config */
    private $config;

    private $greenThreshold;
    private $amberThreshold;
    private $redThreshold;

    const WHITE = 'no-assessment';
    const GREEN = 'passed';
    const AMBER = 'warning';
    const RED = 'fail';

    /** @var MotIdentityInterface */
    private $identity;
    /** @var EventService */
    private $eventService;
    /** @var DateTimeHolder */
    private $dateTimeHolder;
    /** @var AuthorisationServiceInterface */
    private $authService;
    /** @var XssFilter */
    private $xssFilter;

    /**
     * @param EntityManager                      $em
     * @param EnforcementSiteAssessmentValidator $validator
     * @param $config
     * @param MotIdentityInterface $identity
     */
    public function __construct(
        EntityManager $em,
        EnforcementSiteAssessmentValidator $validator,
        $config,
        MotIdentityInterface $identity,
        EventService $eventService,
        AuthorisationServiceInterface $authService,
        XssFilter $xssFilter
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->config = $config;

        $this->greenThreshold = $config['site_assessment']['green'];
        $this->amberThreshold = $config['site_assessment']['amber'];
        $this->redThreshold = $config['site_assessment']['red'];
        $this->identity = $identity;
        $this->eventService = $eventService;
        $this->dateTimeHolder = new DateTimeHolder();
        $this->authService = $authService;
        $this->xssFilter = $xssFilter;
    }

    /**
     * @param $siteId
     *
     * @return EnforcementSiteAssessmentDto
     *
     * @throws NotFoundException
     */
    public function getRiskAssessment($siteId)
    {
        $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT, $siteId);
        $site = $this->getSiteById($siteId);

        if (!$site instanceof Site) {
            throw new NotFoundException('Site not found for ID '.$siteId);
        }

        $assessment = $site->getLastSiteAssessment();

        if (!$assessment instanceof EnforcementSiteAssessment) {
            throw new NotFoundException('No assessment found for site '.$siteId);
        }

        return $this->generateDto($assessment);
    }

    /**
     * @param EnforcementSiteAssessmentDto $dto
     *
     * @return EnforcementSiteAssessmentDto
     */
    public function validateRiskAssessment(EnforcementSiteAssessmentDto $dto)
    {
        $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT, $dto->getSiteId());
        /** @var EnforcementSiteAssessmentDto $dto */
        $dto = $this->xssFilter->filter($dto);
        $this->validator->validate($dto);
        $riskAssessment = $this->generateEnforcementEntity($dto);

        return $this->generateDto($riskAssessment, $dto->getUserIsNotAssessor());
    }

    /**
     * @param EnforcementSiteAssessmentDto $dto
     *
     * @return int
     */
    public function createRiskAssessment(EnforcementSiteAssessmentDto $dto)
    {
        $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT, $dto->getSiteId());
        $dto = $this->xssFilter->filter($dto);
        $this->validator->validate($dto);

        $riskAssessment = $this->generateEnforcementEntity($dto);

        $site = $this->getSiteById($dto->getSiteId());

        $site->setLastSiteAssessment($riskAssessment);

        $riskAssessment->setSite($site);
        $riskAssessment->setAeOrganisationId($site->getOrganisation()->getId());

        $this->em->persist($riskAssessment);
        $this->em->persist($site);

        $examiner = $riskAssessment->getExaminer();
        $examinerName = $this->getUserName();

        if ($examiner) {
            $examinerName = $examiner->getDisplayName().' '.$examiner->getUsername();
        }

        $this->createSiteEvent(
            $site,
            EventTypeCode::UPDATE_SITE_ASSESSMENT_RISK_SCORE,
            sprintf(
                EventDescription::SITE_ASSESSMENT_RISK_SCORE,
                $riskAssessment->getSiteAssessmentScore(),
                $site->getSiteNumber(),
                $site->getName(),
                $examinerName
            ),
            $riskAssessment->getVisitDate()
        );

        $this->em->flush();

        return $riskAssessment->getId();
    }

    /**
     * @param EnforcementSiteAssessmentDto $dto
     *
     * @return EnforcementSiteAssessment
     */
    private function generateEnforcementEntity(EnforcementSiteAssessmentDto $dto)
    {
        $riskAssessment = new EnforcementSiteAssessment();

        $riskAssessment->setTester(
            $this->getPersonByUsername($dto->getTesterUserId())
        );

        $riskAssessment->setVisitDate(DateUtils::toDate($dto->getDateOfAssessment()));
        $riskAssessment->setSiteAssessmentScore($dto->getSiteAssessmentScore());

        if ($dto->getAeRepresentativesUserId()) {
            $examiner = $this->getPersonByUsername($dto->getAeRepresentativesUserId());
            $riskAssessment->setRepresentative($examiner);
            $riskAssessment->setAeRepresentativeName($examiner->getDisplayName());
        } else {
            $riskAssessment->setAeRepresentativeName($dto->getAeRepresentativesFullName());
        }

        $riskAssessment->setAeRepresentativePosition($dto->getAeRepresentativesRole());

        if ($dto->getDvsaExaminersUserId() && $dto->getUserIsNotAssessor() === true) {
            $username = $dto->getDvsaExaminersUserId();
        } else {
            $username = $this->getUsername();
        }

        $dvsaExaminer = $this->getPersonByUsername($username);
        $riskAssessment->setExaminer($dvsaExaminer);

        $site = $this->getSiteById($dto->getSiteId());
        $riskAssessment->setSite($site);
        $riskAssessment->setAeOrganisationId($site->getOrganisation()->getId());

        return $riskAssessment;
    }

    /**
     * @param EnforcementSiteAssessment $assessment
     *
     * @return EnforcementSiteAssessmentDto
     */
    private function generateDto(EnforcementSiteAssessment $assessment, $userIsNotAssessor = false)
    {
        $dto = new EnforcementSiteAssessmentDto();
        $dto->setId($assessment->getId());
        $dto->setAeOrganisationId($assessment->getAeOrganisationId());
        $dto->setSiteAssessmentScore($assessment->getSiteAssessmentScore());
        $dto->setAeRepresentativesFullName($assessment->getAeRepresentativeName());
        $dto->setAeRepresentativesRole($assessment->getAeRepresentativePosition());

        if ($assessment->getRepresentative()) {
            $dto->setAeRepresentativesUserId($assessment->getRepresentative()->getUsername());
        }

        $dto->setTesterUserId($assessment->getTester()->getUsername());
        $dto->setDvsaExaminersUserId($assessment->getExaminer()->getUsername());
        $dto->setDateOfAssessment(DateTimeApiFormat::date($assessment->getVisitDate()));
        $dto->setSiteId($assessment->getSite()->getId());
        $dto->setDvsaExaminersFullName($assessment->getExaminer()->getDisplayName());
        $dto->setTesterFullName($assessment->getTester()->getDisplayName());
        $dto->setUserIsNotAssessor($userIsNotAssessor);

        return $dto;
    }

    /**
     * @param $username
     *
     * @return null|Person
     */
    private function getPersonByUsername($username)
    {
        return $this->em->getRepository(Person::class)->findOneBy(['username' => $username]);
    }

    /**
     * @param $siteId
     *
     * @return null|Site
     */
    private function getSiteById($siteId)
    {
        return $this->em->find(Site::class, $siteId);
    }

    /**
     * @return string
     * @description return ID from zend identity
     */
    private function getUsername()
    {
        return $this->identity->getUsername();
    }

    private function createSiteEvent(Site $site, $eventType, $eventDesc, \DateTime $dateOfEvent = null)
    {
        $event = $this->eventService->addEvent(
            $eventType,
            $eventDesc,
            is_null($dateOfEvent) ? $this->dateTimeHolder->getCurrent(true) : $dateOfEvent
        );

        $eventMap = (new EventSiteMap())
            ->setEvent($event)
            ->setSite($site);

        $this->em->persist($eventMap);
    }
}
