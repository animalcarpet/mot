<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\SiteStatus;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonApi\Model\OutputFormat;
use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApi\Service\AbstractSearchService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApi\Service\Mapper\OdometerReadingMapper;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\CertificateReplacement;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteComment;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaEntities\Repository\SiteStatusRepository;

/**
 * Class MotTestService
 *
 * Service with logic for MOT test
 */
class MotTestService extends AbstractSearchService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    const PENDING_INCOMPLETE_STATUS = 'INCOMPLETE';
    const ODOMETER_NOT_READABLE_UNIT = 'notRead';
    const ODOMETER_NO_ODOMETER_UNIT = 'noOdometer';
    const ODOMETER_MILES_UNIT = 'mi';
    const ODOMETER_KILOMETERS_UNIT = 'km';
    const CONFIG_PARAM_MAX_VISIBLE_VEHICLE_TEST_HISTORY_IN_MONTHS = "maxVisibleVehicleTestHistoryInMonths";
    const OFFSITE_INSPECTION_SITE_NAME = 'DVSA INSPECTION';

    /** @var MotTestRepository */
    private $motTestRepository;
    /** @var MotTestValidator */
    private $motTestValidator;
    /** @var AuthorisationServiceInterface */
    private $authService;
    /** @var ConfigurationRepository */
    private $configurationRepository;
    /** @var \DvsaCommon\Date\DateTimeHolder */
    private $dateTimeHolder;
    /** @var MotTestMapper */
    private $motTestMapper;
    /** @var ReadMotTestAssertion */
    private $readMotTestAssertion;
    /** @var MotTest */
    private $motTest;
    /** @var CreateMotTestService */
    private $createMotTestService;
    /** @var MysteryShopperHelper  */
    private $mysteryShopperHelper;

    /**
     * MotTestService constructor.
     * @param EntityManager $entityManager
     * @param MotTestValidator $motTestValidator
     * @param AuthorisationServiceInterface $authService
     * @param ConfigurationRepository $configurationRepository
     * @param MotTestMapper $motTestMapper
     * @param ReadMotTestAssertion $readMotTestAssertion
     * @param CreateMotTestService $createMotTestService
     * @param MotTestRepository $motTestRepository
     * @param MysteryShopperHelper $mysteryShopperHelper
     */
    public function __construct(
        EntityManager $entityManager,
        MotTestValidator $motTestValidator,
        AuthorisationServiceInterface $authService,
        ConfigurationRepository $configurationRepository,
        MotTestMapper $motTestMapper,
        ReadMotTestAssertion $readMotTestAssertion,
        CreateMotTestService $createMotTestService,
        MotTestRepository $motTestRepository,
        MysteryShopperHelper $mysteryShopperHelper
    ) {
        parent::__construct($entityManager);

        $this->motTestRepository          = $motTestRepository;
        $this->motTestValidator           = $motTestValidator;
        $this->authService                = $authService;
        $this->dateTimeHolder             = new DateTimeHolder();
        $this->configurationRepository    = $configurationRepository;
        $this->motTestMapper              = $motTestMapper;
        $this->readMotTestAssertion       = $readMotTestAssertion;
        $this->createMotTestService       = $createMotTestService;
        $this->mysteryShopperHelper       = $mysteryShopperHelper;
    }

    /**
     * @param $motTestNumber
     * @param bool $clearIdentityMap
     * @return MotTest
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    private function findMotTest($motTestNumber, $clearIdentityMap = false)
    {
        $motTest = $this->getMotTest($motTestNumber, $clearIdentityMap);

        $this->readMotTestAssertion->assertGranted($motTest);

        return $motTest;
    }

    /**
     * @param string $motTestNumber
     * @param bool $minimal optional returns minimal MOT
     * @param bool $clearIdentityMap
     *
     * @throws ForbiddenException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return MotTestDto
     */
    public function getMotTestData($motTestNumber, $minimal = false, $clearIdentityMap = false)
    {
        $motTest = $this->findMotTest($motTestNumber, $clearIdentityMap);

        return $this->extractMotTest($motTest, $minimal);
    }

    /**
     * @param string $motTestNumber
     * @param bool $minimal optional returns minimal MOT
     *
     * @throws ForbiddenException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return MotTestDto
     */
    public function getMotTestDataForRetest($motTestNumber, $minimal = false)
    {
        $motTest = $this->findMotTest($motTestNumber);

        if ($motTest->isCancelled() || $motTest->getStatus() !== MotTestStatusName::FAILED) {
            throw new ForbiddenException('Test number invalid');
        }

        if (!$motTest->getVehicle()) {
            throw new ForbiddenException('No vehicle was found for the MOT test');
        }

        return $this->extractMotTest($motTest, $minimal);
    }

    /**
     * @param MotTest $motTest
     *
     * @return MotTestDto
     */
    private function extractMotTest(MotTest $motTest, $minimal = false)
    {
        return ($minimal)
            ? $this->motTestMapper->mapMotTestMinimal($motTest, true)
            : $this->motTestMapper->mapMotTest($motTest, true);
    }

    /**
     * Get the additional snapshot data for a certificate.
     *
     * @param int $motTestNumber
     *
     * @return array
     */
    public function getAdditionalSnapshotData($motTestNumber)
    {
        $additionalSnapShotData = [];

        $motTest = $this->getMotTest($motTestNumber);

        if ($motTest->getVehicleTestingStation()) {
            $vts = $motTest->getVehicleTestingStation();
            $additionalSnapShotData['TestStationAddress'] = $vts->getAddress();
        }

        if($motTest->getStatus() !== MotTestStatusName::ABORTED
            && $motTest->getStatus() !== MotTestStatusName::ABANDONED){
            $isMysteryShopper = $this->mysteryShopperHelper->isMysteryShopperToggleEnabled() && $motTest->getMotTestType()->getCode() === MotTestTypeCode::MYSTERY_SHOPPER;
            if (!$isMysteryShopper) {
                $odometerHistoryForVehicle = $this->motTestRepository->getOdometerHistoryForVehicleId(
                    $motTest->getVehicle()->getId(),
                    $motTest->getStartedDate()
                );
            }
            else {
                $odometerHistoryForVehicle = $this->motTestRepository->getOdometerHistoryForVehicleId(
                    $motTest->getVehicle()->getId(),
                    $motTest->getStartedDate(),
                    [MotTestTypeCode::MYSTERY_SHOPPER],
                    1
                );
            }
            $additionalSnapShotData['OdometerReadings'] = (new OdometerReadingMapper())->manyToDtoFromArray(
                $odometerHistoryForVehicle
            );
        }

        return $additionalSnapShotData;
    }

    /**
     * @param array $data
     * @return MotTest
     */
    public function createMotTest(array $data)
    {
        return $this->createMotTestService->create($data);
    }

    /**
     * Update the location of a reinspection. Only one or the other of the two parameters
     * is expected to be given.
     *
     * @param $username          String the username making the request
     * @param $motTestId         Int the row of the database to be updated
     * @param $siteid            String contains the existing site id
     * @param $locationSiteText  String contains the free format text for an offsite reinspection.
     *
     * @return true if the update was successful
     */
    public function updateMotTestLocation($username, $motTestId, $siteid = null, $locationSiteText = null)
    {
        $motTest = $this->getMotTest($motTestId);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        if ($locationSiteText) {
            $siteid = $this->createOffsiteComment($locationSiteText, $username, SiteTypeCode::OFFSITE);
        }

        if ($siteid && $motTest) {
            /** @var Site $vts */
            $vts = $this->entityManager->find(Site::class, $siteid);
            $motTest->setVehicleTestingStation($vts);
            if($vts->getOrganisation()) {
                $motTest->setOrganisation($vts->getOrganisation());
            }
            $this->entityManager->persist($motTest);
            $this->entityManager->flush();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a new tuple of (Site,Comment) to record that the re-inspection took place
     * at a non-VTS site location.
     *
     * @param string $locationSiteText contains user entered ad-hoc text
     * @param string $username the username making the request
     * @param string $type
     *
     * @return int|null
     */
    public function createOffsiteComment($locationSiteText, $username, $type = SiteTypeCode::VEHICLE_TESTING_STATION)
    {
        try {
            /** @var $user \DvsaEntities\Entity\Person */
            $user = $this->getUser($username);
            /** @var SiteTypeRepository $siteTypeRepo */
            $siteTypeRepo = $this->entityManager->getRepository(SiteType::class);
            /** @var $siteType \DvsaEntities\Entity\SiteType */
            $siteType = $siteTypeRepo->getByCode($type);

            $riSite = new Site();

            // Default status for Site is Approved only on creation (see: Alisdar Cameron)
            $approvedStatus = SiteStatusCode::APPROVED;

            /** @var SiteStatusRepository $statusRepo */
            $statusRepo = $this->entityManager->getRepository(SiteStatus::class);
            /** @var $status \DvsaEntities\Entity\SiteStatus */
            $status = $statusRepo->getByCode($approvedStatus);

            $riSite->setStatus($status);

            $riComment = new Comment();
            $siteComment = new SiteComment();

            $riComment->setComment($locationSiteText);
            $riComment->setCommentAuthor($user);

            $riSite->setReinspectionSiteNumber();
            $riSite->setType($siteType);
            $riSite->setName(self::OFFSITE_INSPECTION_SITE_NAME);

            $siteComment->setSite($riSite);
            $siteComment->setComment($riComment);
            $riSite->addSiteComment($siteComment);

            $this->entityManager->persist($riComment);
            $this->entityManager->persist($riSite);
            $this->entityManager->persist($siteComment);

            $this->entityManager->flush();

            return $riSite->getId();
        } catch (\Exception $e) {
            error_log(
                "MotTestService::createOffsiteComment: failed: [" .
                print_r($e->getMessage(), true) .
                "]"
            );
        }
        return null;
    }

    /**
     * Update one Person Test details on a MOT Re-inspection.
     *
     * @param $motTestNumber
     * @param $onePersonTest
     * @param $onePersonReInspection
     *
     * @return bool
     */
    public function updateOnePersonTest($motTestNumber, $onePersonTest, $onePersonReInspection)
    {
        try {
            $this->authService->assertGranted(PermissionInSystem::MOT_TEST_REINSPECTION_PERFORM);

            $motTest = $this->getMotTest($motTestNumber);
            $motTest->setOnePersonTest($onePersonTest);
            $motTest->setOnePersonReInspection($onePersonReInspection);
            $this->entityManager->persist($motTest);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $username
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     *
     * @return null|\DvsaEntities\Entity\Person
     */
    public function getUser($username)
    {
        $user = $this->entityManager->getRepository(Person::class)->findOneBy(
            ['username' => $username]
        );
        if (!$user) {
            throw new BadRequestException('Invalid user', BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        return $user;
    }

    /**
     * Return a replacement certificate record for an MOT ID.
     *
     * @param integer $motTestId Test ID of MOT to fetch certificate
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     *
     * @return null|\DvsaEntities\Entity\CertificateReplacement
     */
    public function getReplacementCertificate($motTestId)
    {
        return $this->entityManager->getRepository(CertificateReplacement::class)->findOneBy(
            ['motTest' => $motTestId]
        );
    }

    public function updateDocument($motTestNumber, $documentId)
    {
        $motTest = $this->getMotTest($motTestNumber);
        $motTest->setDocument($documentId);
        $this->entityManager->persist($motTest);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get an MotTest entity from the test number. Result will be cached.
     *
     * @param string $motTestNumber
     * @param bool $clearIdentityMap
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return \DvsaEntities\Entity\MotTest
     */
    public function getMotTest($motTestNumber, $clearIdentityMap = false)
    {
        if ($this->motTest instanceof MotTest
            && $this->motTest->getNumber() === (string)$motTestNumber
        ) {
            return $this->motTest;
        }

        if ($clearIdentityMap) {
            $this->getEntityManager()->clear();
        }

        $this->motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);

        return $this->motTest;
    }

    private function extractMotTests($motTests)
    {
        $motTestData = [];
        foreach ($motTests as $motTest) {
            $motTestData[] = $this->extractMotTest($motTest);
        }

        return $motTestData;
    }

    /**
     * Get Mot Tests by Vehicle Registration Mark (vrm or registration).
     *
     * @param string $vrm
     * @param int $maxResults
     *
     * @return array|null
     */
    public function getMotTestsByVrm($vrm, $maxResults = 100)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_LIST);
        $vehicle = $this->entityManager->getRepository(Vehicle::class)
            ->findOneBy(['registration' => $vrm]);

        if (!$vehicle instanceof Vehicle) {
            return null;
        }

        $motTests = $this->motTestRepository->getLatestMotTestsByVehicleId($vehicle->getId(), $maxResults);

        return !empty($motTests) ? $this->extractMotTests($motTests) : null;
    }

    /**
     * Retrieve the relevant document IDs of any certificates (VT20, VT30)
     * associated with a given MOT.
     *
     * @param array $motTest Mot Test Data
     *
     * @return array
     */
    public function getCertificateIds(MotTestDto $motTestData)
    {
        $details = [$motTestData->getDocument()];

        $prsMotTestNr = $motTestData->getPrsMotTestNumber();

        if ($prsMotTestNr !== null
            && (
                $motTestData->getTestType() instanceof MotTestTypeDto
                && !MotTestType::isVeAdvisory($motTestData->getTestType()->getCode())
            )
        ) {
            $motTestData = $this->getMotTestData($prsMotTestNr);

            // the PRS test is actually our 'pass', and as such should be considered
            // our primary certificate; hence the unshift rather than push
            array_unshift($details, $motTestData->getDocument());
        }

        return $details;
    }

    /**
     * Provides the ability to check the users access to the current search.
     */
    protected function checkPermissions()
    {
        // TODO: Implement checkPermissions() method.
    }

    /**
     * Performs the actual search using the repository.
     *
     * @param SearchParam $params
     * @param OutputFormat $format
     *
     * @return mixed|void
     */
    protected function repositorySearch(SearchParam $params, OutputFormat $format)
    {
        // TODO: Implement repositorySearch() method.
    }

    /**
     * Returns whether there is an existing in progress test for a given vehicle id.
     *
     * @param $vehicleId
     *
     * @return bool
     */
    public function isTestInProgressForVehicle($vehicleId)
    {
        $existingTest = $this->motTestRepository->findInProgressTestForVehicle($vehicleId);
        if ($existingTest !== null) {
            $this->readMotTestAssertion->assertGranted($existingTest);

            return true;
        }

        return false;
    }

    /**
     * Check to see if the current user can print the certificate(s) for a  MOT test.
     *
     * @param MotTestDto $motTest
     *
     * @return boolean
     */
    public function canPrintCertificateForMotTest(MotTestDto $motTest)
    {
        $siteId = ArrayUtils::tryGet($motTest->getVehicleTestingStation(), 'id');
        if ($this->authService->isGrantedAtSite(PermissionAtSite::CERTIFICATE_PRINT, $siteId)) {
            return true;
        }

        return $this->readMotTestAssertion->isMotTestOwnerForDto($motTest);
    }

    public function getLatestPassedTestByVehicleId($vehicleId)
    {
        $lastMotTest = $this->motTestRepository
            ->getLatestMotTestByVehicleIdAndResult($vehicleId, MotTestStatusName::PASSED);

        return $lastMotTest;
    }
}
