<?php

namespace DvsaMotApi\Service;

use DataCatalogApi\Service\DataCatalogService;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\EnfSiteVisitOutcomeId;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaMotApi\Model\SiteAssessmentValidator;
use OrganisationApi\Service\AuthorisedExaminerService;
use SiteApi\Service\SiteService;

/**
 * Class EnforcementMotTestResultService.
 */
class EnforcementSiteAssessmentService extends AbstractService
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const ENFORCEMENT_VISIT_OUTCOME_ABANDONED = 3;

    /**
     * @var AuthorisationServiceInterface
     */
    protected $authService;

    /**
     * @var TesterService
     */
    protected $testerService;

    /**
     * @var SiteService
     */
    protected $vehicleTestingStationService;

    /**
     * @var AuthorisedExaminerService
     */
    protected $authorisedExaminerService;

    /**
     * @var DataCatalogService
     */
    protected $catalogService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @param EntityManager                 $entityManager
     * @param DoctrineObject                $objectHydrator
     * @param AuthorisationServiceInterface $authService
     * @param TesterService                 $testerService
     * @param SiteService                   $vehicleTestingStationService
     * @param AuthorisedExaminerService     $authorisedExaminerService
     * @param DataCatalogService            $catalogService
     * @param UserService                   $user
     */
    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService,
        TesterService $testerService,
        SiteService $vehicleTestingStationService,
        AuthorisedExaminerService $authorisedExaminerService,
        DataCatalogService $catalogService,
        UserService $user
    ) {
        parent::__construct($entityManager);
        $this->objectHydrator = $objectHydrator;
        $this->repository = $this->entityManager->getRepository(\DvsaEntities\Entity\EnforcementSiteAssessment::class);
        $this->authService = $authService;
        $this->testerService = $testerService;
        $this->vehicleTestingStationService = $vehicleTestingStationService;
        $this->authorisedExaminerService = $authorisedExaminerService;
        $this->catalogService = $catalogService;
        $this->userService = $user;
    }

    /**
     * Return an array.
     *
     * @param int $id id
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getData($id)
    {
        $this->authService->assertGranted(PermissionInSystem::ENFORCEMENT_SITE_ASSESSMENT);

        $result = $this->repository->findOneBy(
            ['id' => $id]
        );
        if (!$result) {
            throw new NotFoundException('EnforcementSiteAssessment', $id);
        }

        return $this->extract($result);
    }

    /**
     * Create db records.
     *
     * @param array  $data     data
     * @param string $username username
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function create($data, $username)
    {
        $this->authService->assertGranted(PermissionInSystem::ENFORCEMENT_SITE_ASSESSMENT);

        $validator = new SiteAssessmentValidator(
            $data,
            $this->vehicleTestingStationService,
            $this->authorisedExaminerService,
            $this->testerService,
            $this->catalogService,
            $this->userService
        );

        if (count($validator->getErrors())) {
            throw new BadRequestExceptionWithMultipleErrors($validator->getErrors());
        }

        if (isset($data['formAction']) && 'save' == $data['formAction']) {
            $user = $this->userService->getUserByUsername($data[SiteAssessmentValidator::F_TESTER_ID]);
            $tester = $this->testerService->getTesterByUserId($user->getId());
            $vts = $this->entityManager
                ->getRepository(\DvsaEntities\Entity\Site::class)
                ->findOneBy(['siteNumber' => $validator->getSiteNumber()]);

            /*
             * TODO
             * The Site Assessment should be linked to a Person rather than 'AE'.
             * Dominik Sipowicz will sort out
             */
            $dql = $this->entityManager->createQuery(
                    'SELECT
                        ae
                      FROM
                        DvsaEntities\Entity\AuthorisationForAuthorisedExaminer ae
                        JOIN ae.organisation o
                        JOIN o.positions pos
                        JOIN pos.person p
                      WHERE
                        p.username = :username'
            );
            $dql->setParameter('username', $data[SiteAssessmentValidator::F_AE_REP_ID]);
            $ae = $dql->getFirstResult();

            $visitDate = $data[SiteAssessmentValidator::F_YEAR]
                .'-'.str_pad($data[SiteAssessmentValidator::F_MONTH], 2, '0', STR_PAD_LEFT)
                .'-'.str_pad($data[SiteAssessmentValidator::F_DAY], 2, '0', STR_PAD_LEFT);

            $visitOutcome = $this->entityManager
                ->getRepository(\DvsaEntities\Entity\EnforcementVisitOutcome::class)
                ->find($data[SiteAssessmentValidator::F_VISIT_OUTCOME]);

            $result = new EnforcementSiteAssessment();
            $result
                ->setSiteAssessmentScore($data[SiteAssessmentValidator::F_SITE_SCORE])
                ->setAdvisoryIssued($data[SiteAssessmentValidator::F_ADVISORY_ISSUED])
                ->setTester($tester)
                ->setAeRepresentativePosition($data[SiteAssessmentValidator::F_AE_REP_POSITION])
                ->setVisitDate(DateUtils::toDate($visitDate))
                ->setVehicleTestingStation($vts)
                ->setAuthorisedExaminer($ae)
                ->setVisitOutcome($visitOutcome);

            $this->entityManager->persist($result);
            $this->entityManager->flush();

            if ((int) $visitOutcome->getId() != EnfSiteVisitOutcomeId::ABANDONED) {
                if ($this->compareLastAssessmentDate($vts, $result)) {
                    $vts->setLastSiteAssessment($result);
                    $this->entityManager->persist($vts);
                    $this->entityManager->flush();
                }
            }

            return ['id' => $result->getId()];
        } else {
            return ['validated' => true];
        }
    }

    /**
     * Test the last assessment date of visit to know if we update the lastassessment id in the site table.
     * (The last assessment should always be the more recent one).
     *
     * @param $vts
     * @param $assessment
     *
     * @return bool
     */
    protected function compareLastAssessmentDate($vts, $assessment)
    {
        if ($vts->getLastSiteAssessment()) {
            $objectHydrator = $this->objectHydrator;

            $lastSiteAssessment = $objectHydrator->extract(
                $vts->getLastSiteAssessment()
            );
            if ($lastSiteAssessment['visitDate'] > $assessment->getVisitDate()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract.
     *
     * @param EnforcementSiteAssessment $siteAssessment
     *
     * @return array
     */
    protected function extract(EnforcementSiteAssessment $siteAssessment)
    {
        $result = $this->objectHydrator->extract($siteAssessment);

        if ($siteAssessment->getVehicleTestingStation()) {
            $vts = $this->objectHydrator->extract($siteAssessment->getVehicleTestingStation());

            $vts['authorisedExaminer'] = $this->objectHydrator->extract(
                $siteAssessment->getVehicleTestingStation()->getAuthorisedExaminer()
            );
            $vts['address'] = $this->objectHydrator->extract($siteAssessment->getVehicleTestingStation()->getAddress());

            $result['vehicleTestingStation'] = $vts;
        }
        if ($siteAssessment->getAuthorisedExaminer()) {
            $result['authorisedExaminer'] = $this->objectHydrator->extract($siteAssessment->getAuthorisedExaminer());
        }
        if ($siteAssessment->getTester()) {
            $tester = $this->objectHydrator->extract($siteAssessment->getTester());
            $tester['user'] = $this->objectHydrator->extract($siteAssessment->getTester());
            $result['tester'] = $tester;
        }
        if ($siteAssessment->getVisitOutcome()) {
            $result['visitOutcome'] = $this->objectHydrator->extract($siteAssessment->getVisitOutcome());
        }
        if ($siteAssessment->getCreatedBy()) {
            $result['createdBy'] = $siteAssessment->getCreatedBy()->getUsername();
        }
        if ($siteAssessment->getLastUpdatedBy()) {
            $result['lastUpdatedBy'] = $siteAssessment->getLastUpdatedBy()->getUsername();
        }
        if ($siteAssessment->getVisitDate()) {
            $result['visitDate'] = DateTimeApiFormat::date($siteAssessment->getVisitDate());
        }
        if ($siteAssessment->getCreatedOn()) {
            $result['createdOn'] = DateTimeApiFormat::dateTime($siteAssessment->getCreatedOn());
        }
        if ($siteAssessment->getLastUpdatedOn()) {
            $result['lastUpdatedOn'] = DateTimeApiFormat::dateTime($siteAssessment->getLastUpdatedOn());
        }

        return $result;
    }
}
