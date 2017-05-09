<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthentication\IdentityProvider;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaAuthorisation\Service\RoleProviderService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaMotApi\Mapper\VtsAddressMapper;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Class TesterService.
 */
class TesterService extends AbstractService
{
    /** @var PersonRepository $personRepository */
    private $personRepository;
    /** @var MotTestRepository $motTestRepository */
    private $motTestRepository;
    /** @var SiteRepository $siteRepository */
    private $siteRepository;
    private $objectHydrator;

    /** @var AuthorisationServiceInterface $authService */
    private $authService;
    private $specialNoticeService;

    private $roleProviderService;

    private $identityProvider;

    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService,
        SpecialNoticeService $specialNoticeService,
        RoleProviderService $roleProviderService,
        IdentityProvider $identityProvider,
        SiteRepository $siteRepository
    ) {
        parent::__construct($entityManager);
        $this->siteRepository = $siteRepository;
        $this->personRepository = $this->entityManager->getRepository(Person::class);
        $this->motTestRepository = $this->entityManager->getRepository(MotTest::class);
        $this->objectHydrator = $objectHydrator;
        $this->authService = $authService;
        $this->specialNoticeService = $specialNoticeService;
        $this->roleProviderService = $roleProviderService;
        $this->identityProvider = $identityProvider;
    }

    public function getTesterData($id, $onlyVtsSlotBalance = false)
    {
        $this->authService->assertGranted(PermissionInSystem::TESTER_READ);
        if (!$this->authService->isAuthenticatedAsPerson($id) && !$this->authService->isGranted(PermissionInSystem::TESTER_READ_OTHERS)) {
            throw new UnauthorisedException('You are not authorised to access this resource');
        }

        $tester = $this->getTesterById($id);

        return $this->extract($tester, $onlyVtsSlotBalance);
    }

    /**
     * Get Tester by id.
     *
     * @param int $personId
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Person
     */
    public function getTesterById($personId)
    {
        return $this->personRepository->get($personId);
    }

    /**
     * @param $userId
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return Person
     */
    public function getTesterByUserId($userId)
    {
        /** @var Person $tester */
        $tester = $this->personRepository->find($userId);

        if (!$tester || !$tester->isTester()) {
            throw new NotFoundException('Tester with personId', $userId);
        }

        return $tester;
    }

    public function isTester($userId)
    {
        /** @var Person $p */
        $p = $this->personRepository->find($userId);

        return $p->isTester();
    }

    /**
     * Verifies if a person with the given id is a qualified tester.
     *
     * @param $personId
     *
     * @return bool
     */
    public function isQualifiedTester($personId)
    {
        /** @var Person $p */
        $p = $this->personRepository->find($personId);

        return $p->isQualifiedTester();
    }

    public function verifyTesterAllowedToTestClass($class)
    {
        $countOverdueSpecialNotices = $this->specialNoticeService->countOverdueSpecialNoticesForClass($class);

        if ($countOverdueSpecialNotices > 0) {
            return false;
        }

        return true;
    }

    public function isTesterActiveByUser(Person $person)
    {
        return $this->authService->personHasRole(
            $person,
            'TESTER-ACTIVE'
        );
    }

    public function getTesterDataByUserId($personId)
    {
        $this->authService->assertGranted(PermissionInSystem::TESTER_READ);

        return $this->extract($this->getTesterByUserId($personId));
    }

    /**
     * Finds a tester for a given certificate number.
     *
     * @param string $certificateNumber
     *
     * @return array
     */
    public function findTesterDataByCertificateNumber($certificateNumber)
    {
        try {
            $motTest = $this->motTestRepository->getMotTestByNumber($certificateNumber);
        } catch (NotFoundException $e) {
            return [];
        }
        $tester = $motTest->getTester();

        return $this->extract($tester);
    }

    /**
     * Finds in progress motTestNumber for a tester.
     *
     * @param $personId
     *
     * @return int|null
     */
    public function findInProgressTestIdForTester($personId)
    {
        $this->assertGrantedToFindInProgress($personId);

        return $this->motTestRepository->findInProgressTestNumberForPerson($personId);
    }

    /**
     * Finds in progress test for a tester.
     *
     * @param $personId
     *
     * @return MotTest
     */
    public function findInProgressTestForTester($personId)
    {
        $this->assertGrantedToFindInProgress($personId);

        return $this->motTestRepository->findInProgressTestForPerson($personId);
    }

    /**
     * Return in progress demo test number for the given person.
     *
     * @see MotTestRepository::findInProgressDemoTestForPerson for different type of demo testst
     *
     * @param int  $personId
     * @param bool $routine  To set the demo test type
     *
     * @return null|string
     *
     * @throws UnauthorisedException
     */
    public function findInProgressDemoTestNumberForTester($personId, $routine = false)
    {
        $this->assertGrantedToFindInProgress($personId);

        return $this->motTestRepository->findInProgressDemoTestNumberForPerson($personId, $routine);
    }

    /**
     * @param int $personId
     *
     * @return null|string
     *
     * @throws UnauthorisedException
     */
    public function findInProgressNonMotTestNumberForVehicleExaminer($personId)
    {
        if (!$this->authService->isGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM)) {
            throw new UnauthorisedException('Assertion failed. Cannot read in progress non-MOT test.');
        }

        return $this->motTestRepository->findInProgressNonMotTestNumberForPerson($personId);
    }

    /**
     * Returns a collection of VehicleTestingStations where a tester has a role of 'TESTER'.
     *
     * @param int $testerId
     *
     * @throws UnauthorisedException
     *
     * @return array
     */
    public function getVehicleTestingStationsForTester($testerId)
    {
        // Same authorisation check as in TesterService::getTesterData():
        $this->authService->assertGranted(PermissionInSystem::TESTER_READ);
        if (!$this->authService->isAuthenticatedAsPerson($testerId)
            && !$this->authService->isGranted(PermissionInSystem::TESTER_READ_OTHERS)) {
            throw new UnauthorisedException('You are not authorised to access this resource');
        }
        $result = [];
        $sites = $this->siteRepository->findForPersonIdWithRoleCodeAndStatusCode(
            $testerId,
            SiteBusinessRoleCode::TESTER,
            BusinessRoleStatusCode::ACTIVE
        );
        foreach ($sites as $site) {
            $siteArray = [];
            $siteArray['name'] = $site->getName();
            $siteArray['siteNumber'] = $site->getSiteNumber();
            $siteArray['dualLanguage'] = $site->getDualLanguage();
            $siteArray['scottishBankHoliday'] = $site->getScottishBankHoliday();
            $siteArray['latitude'] = $site->getLatitude();
            $siteArray['longitude'] = $site->getLongitude();
            $siteArray['id'] = $site->getId();
            $siteArray['address'] = VtsAddressMapper::mapToVtsTitleString($site->getAddress());
            $result[] = $siteArray;
        }

        return $result;
    }

    private function assertGrantedToFindInProgress($personId)
    {
        if (!$this->authService->isGranted(PermissionInSystem::TESTER_READ)) {
            if ($personId != $this->identityProvider->getIdentity()->getUserId()) {
                throw new UnauthorisedException('Assertion failed. Cannot read test in progress of other testers.');
            }
        }
    }

    /**
     * @param Person $tester
     *
     * @return array
     */
    private function extract(Person $tester, $onlyVtsSlotBalance = false)
    {
        $testerData = [];
        if (!$onlyVtsSlotBalance) {
            $testerData = $this->objectHydrator->extract($tester);

            unset($testerData['roles']);
            $testerData['roles'] = [];

            $testerData['roles'] = $this->roleProviderService->getRolesForPerson($tester);

            unset($testerData['vehicleTestingStations']);
            $testerData['vtsSites'] = [];

            if (!empty($tester)) {
                $testerData['user'] = $this->objectHydrator->extract($tester);
                $testerData['user']['displayName'] = $tester->getDisplayName();
            }
        }
        $role = $this->entityManager->getRepository(\DvsaEntities\Entity\SiteBusinessRole::class)->findOneBy(
            [
                'code' => SiteBusinessRoleCode::TESTER,
            ]
        );
        /** @var \DvsaEntities\Repository\SiteRepository $siteRepository */
        $siteRepository = $this->entityManager->getRepository(\DvsaEntities\Entity\Site::class);
        $sites = $siteRepository->findForPersonWithRole($tester, $role, BusinessRoleStatusCode::ACTIVE);
        foreach ($sites as $vehicleTestingStation) {
            $vehicleTestingStationData = $this->objectHydrator->extract($vehicleTestingStation);
            $vehicleTestingStationData['address'] = VtsAddressMapper::mapToVtsTitleString(
                $vehicleTestingStation->getAddress()
            );
            unset($vehicleTestingStationData['roles']);
            $ae = $vehicleTestingStation->getAuthorisedExaminer();
            // Set slots for backward compatibility (used to be associated with VTS rather than AE)

            if ($this->authService->isGranted(PermissionInSystem::SLOTS_VIEW)) {
                $slots = 0;
                $slotsWarning = 0;

                $organisation = $vehicleTestingStation->getOrganisation();

                if (is_object($organisation)) {
                    $slots = $organisation->getSlotBalance();
                    $slotsWarning = $organisation->getSlotsWarning();
                }

                $vehicleTestingStationData['slots'] = $slots;
                $vehicleTestingStationData['slotsWarning'] = $slotsWarning;
            }

            if (is_object($ae)) {
                $vehicleTestingStationData['aeId'] = $ae->getId();
            }

            unset($vehicleTestingStationData['authorisedExaminer']); // Not hydrated so there's no point setting it.

            $testerData['vtsSites'][] = $vehicleTestingStationData;
        }

        unset($testerData['authorisationsForTestingMot']);

        if (!$onlyVtsSlotBalance) {
            /** @var AuthorisationForTestingMot $authorisationForTestingMot */
            foreach ($tester->getAuthorisationsForTestingMot() as $authorisationForTestingMot) {
                $authorisationData['id'] = $authorisationForTestingMot->getId();
                $authorisationData['vehicleClassCode'] = $authorisationForTestingMot->getVehicleClass()->getCode();
                $authorisationData['statusCode'] = $authorisationForTestingMot->getStatus()->getCode();
                $testerData['authorisationsForTestingMot'][] = $authorisationData;
            }

            $testInProgress = $this->motTestRepository->findInProgressTestForPerson($tester->getId());
            if ($testInProgress) {
                $testerData['testInProgress'] = [
                    'id' => $testInProgress->getId(),
                    'vts' => [
                        'id' => $testInProgress->getVehicleTestingStation()->getId(),
                    ],
                ];
            }
        }

        return $testerData;
    }
}
