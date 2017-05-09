<?php

namespace Dvsa\Mot\AuditApiIntegrationTest;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Identity;
use DvsaAuthentication\IdentityProvider;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestService;
use PHPUnit_Framework_MockObject_MockObject;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;

/**
 * Integration test to assert KDD069-related triggers are effective.
 */
class AuditTriggerTest extends AbstractServiceTestCase
{
    const TESTER1_USERNAME = 'tester1';
    const TESTER1_ID = 105;
    const TESTER2_USERNAME = 'tester2';
    const TESTER2_ID = 118;
    const VEHICLE_ID = 1;
    const SITE_1_ID = 1;
    const SITE_2_ID = 2;

    /** @var MotIdentityProviderInterface */
    private $identityProviderMock;
    /** @var AuthenticationServiceInterface */
    private $authenticationServiceMock;
    /** @var AuthorisationServiceInterface */
    private $authorisationServiceMock;
    /** @var Identity */
    private $identity;
    /** @var MotTestService */
    private $motTestService;

    public function setUp()
    {
        Bootstrap::setupServiceManager();
        $sm = $this->getServiceManager()->setAllowOverride(true);

        $sm->setService(
            MotIdentityProviderInterface::class,
            $this->identityProviderMock = $this->getIdentityProviderMock()
        );
        $sm->setService(
            'DvsaAuthenticationService',
            $this->authenticationServiceMock = $this->getAuthenticationServiceMock()
        );
        $sm->setService(
            'DvsaAuthorisationService',
            $this->authorisationServiceMock = $this->getAuthorisationServiceMock()
        );
    }

    public function tearDown()
    {
        $this->clearExistingMots();
    }

    /**
     * Ensures auditable columns are affected solely by KDD069 strategy.
     */
    public function testSessionVarsEffective()
    {
        $mot = $this->createOrGetMotTest();
        $this->assertEquals(self::TESTER1_ID, $mot->getCreatedBy()->getId());
    }

    /**
     * Ensures trigger-based auditable columns are correct when an MOT record is inserted.
     */
    public function testMotTestTableInsert()
    {
        $this->setKDD069DbSessionVars();
        $this->clearExistingMots(); //ensure we're doing an insert
        $mot = $this->createOrGetMotTest();
        $this->assertEquals($mot->getLastUpdatedBy()->getId(), self::TESTER1_ID);
        $this->assertEquals($mot->getCreatedBy()->getId(), self::TESTER1_ID);
        $this->assertEquals($mot->getVersion(), 2); // new mots are immediately updated to have unique ids
    }

    /**
     * Ensures trigger-based auditable columns are updated when an MOT record is updated.
     */
    public function testMotTestTableUpdate()
    {
        /** @var Person $person */
        $person = $this->getEntityManager()->getRepository(Person::class)->find(self::TESTER2_ID);
        $this->setKDD069DbSessionVars($person);

        $mot = $this->createOrGetMotTest();
        $versionBeforeUpdate = $mot->getVersion();
        $this->getMotTestService()->updateMotTestLocation(self::TESTER1_USERNAME, $mot->getNumber(), self::SITE_2_ID);
        $this->getEntityManager()->flush();

        $mot = $this->createOrGetMotTest(true);
        $this->assertEquals($mot->getCreatedBy()->getId(), self::TESTER2_ID);
        $this->assertEquals($mot->getLastUpdatedBy()->getId(), self::TESTER2_ID);
        $this->assertEquals(self::SITE_2_ID, $mot->getVehicleTestingStation()->getId());
        $this->assertEquals(($versionBeforeUpdate + 1), $mot->getVersion());
    }

    /**
     * @return MotTest
     */
    protected function createOrGetMotTest($hardReload = false)
    {
        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $this->getEntityManager()->getRepository(MotTest::class);
        $motTest = $motTestRepository->findInProgressTestForVehicle(self::VEHICLE_ID);
        if ($motTest) {
            if ($hardReload) {
                $this->getEntityManager()->refresh($motTest);
            }

            return $motTest;
        }

        $mot = $this->getMotTestService()->createMotTest($this->getTestData());
        $this->getEntityManager()->detach($mot); // proxy instance for created_by assoc unless detached and find()
        return $this->getEntityManager()->getRepository(MotTest::class)->find($mot->getId());
    }

    protected function clearExistingMots()
    {
        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $this->getEntityManager()->getRepository(MotTest::class);
        $motTest = $motTestRepository->findInProgressTestForVehicle(self::VEHICLE_ID);
        if ($motTest) {
            $entityManager = $this->getEntityManager();
            $entityManager->remove($motTest);
            $entityManager->flush();
        }
    }

    /**
     * @param Person $tester
     */
    protected function setKDD069DbSessionVars(Person $tester = null)
    {
        if (!$tester) {
            $tester = $this->identityProviderMock->getIdentity()->getPerson();
        }
        $auditService = new HistoryAuditService(
            $this->getEntityManager(),
            $tester
        );
        $auditService->execute();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|AuthorisationServiceInterface
     */
    protected function getAuthorisationServiceMock()
    {
        $authorizationServiceMock = XMock::of(AuthorisationServiceInterface::class);
        $authorizationServiceMock->expects($this->any())->method('isGranted')->willReturn(true);

        return $authorizationServiceMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|IdentityProvider
     */
    protected function getIdentityProviderMock()
    {
        $identity = $this->getIdentity();
        $identityProviderMock = XMock::of(IdentityProvider::class);
        $identityProviderMock->expects($this->any())->method('getIdentity')->willReturn($identity);

        return $identityProviderMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|AuthenticationService
     */
    protected function getAuthenticationServiceMock()
    {
        $identity = $this->getIdentity();
        $identityProviderMock = XMock::of(AuthenticationService::class);
        $identityProviderMock->expects($this->any())->method('getIdentity')->willReturn($identity);

        return $identityProviderMock;
    }

    /**
     * @return MotTestService
     */
    protected function getMotTestService()
    {
        if ($this->motTestService) {
            return $this->motTestService;
        }

        $this->motTestService = $this->getServiceManager()->get('MotTestService');

        return $this->motTestService;
    }

    /**
     * @return Identity
     */
    protected function getIdentity()
    {
        if ($this->identity) {
            return $this->identity;
        }

        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        /** @var Person $tester */
        $tester = $em->getRepository(Person::class)->find(self::TESTER1_ID);
        $this->identity = new Identity($tester);

        return $this->identity;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
    }

    /**
     * @return array
     */
    protected function getTestData()
    {
        return [
            'vehicleId' => self::VEHICLE_ID,
            'vehicleTestingStationId' => self::SITE_1_ID,
            'primaryColour' => ColourCode::GREY,
            'secondaryColour' => ColourCode::NOT_STATED,
            'fuelTypeId' => FuelTypeCode::PETROL,
            'vehicleClassCode' => VehicleClassCode::CLASS_4,
            'hasRegistration' => true,
            'oneTimePassword' => null,
        ];
    }
}
