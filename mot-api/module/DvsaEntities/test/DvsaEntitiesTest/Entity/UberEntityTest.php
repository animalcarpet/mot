<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity;

/**
 * Class UberEntityTest
 *
 * Class run generic tests on entites. Verifies simple getters/setters
 */
class UberEntityTest extends \PHPUnit_Framework_TestCase
{
    private $excludeMethods = ['getRoles'];

    protected $stack = [];

    public function setUp()
    {
        $this->stack[] = new Entity\Address();
        $this->stack[] = new Entity\ApplicationAuthorisedExaminer();
        $this->stack[] = new Entity\ApplicationForAuthorisationTestingMot();
        $this->stack[] = new Entity\ApplicationLock('12345', new Entity\Person());
        $this->stack[] = new Entity\Application();
        $this->stack[] = new Entity\ApplicationSectionState('12345', 'foo');
        $this->stack[] = new Entity\ApplicationStatus();
        $this->stack[] = new Entity\ApplicationTesterApplication();
        $this->stack[] = new Entity\ApplicationTester();
        $this->stack[] = new Entity\AuthorisationForTestingMot();
        $this->stack[] = new Entity\AuthorisationForTestingMotAtSite();
        $this->stack[] = new Entity\AuthorisationForTestingMotStatus();
        $this->stack[] = new Entity\AuthorisedExaminerDesignatedManagerApplication();
        $this->stack[] = new Entity\AuthorisedExaminerDesignatedManager();
        $this->stack[] = new Entity\AuthorisationForAuthorisedExaminer();
        $this->stack[] = new Entity\AuthForAeStatus();
        $this->stack[] = new Entity\BodyType();
        $this->stack[] = new Entity\BrakeTestResultClass12();
        $this->stack[] = new Entity\BrakeTestResultClass3AndAbove();
        $this->stack[] = new Entity\BrakeTestResultServiceBrakeData();
        $this->stack[] = new Entity\BrakeTestType();
        $this->stack[] = new Entity\CensorBlacklist();
        $this->stack[] = new Entity\CertificateChangeDifferentTesterReason();
        $this->stack[] = new Entity\CertificateReplacement();
        $this->stack[] = new Entity\Colour();
        $this->stack[] = new Entity\Comment();
        $this->stack[] = new Entity\Configuration();
        $this->stack[] = new Entity\ContactDetail();
        $this->stack[] = new Entity\PhoneContactType();
        $this->stack[] = new Entity\Country();
        $this->stack[] = new Entity\CountryOfRegistration();
        $this->stack[] = new Entity\DvlaVehicleImportChangeLog();
        $this->stack[] = new Entity\DvlaVehicle();
        $this->stack[] = new Entity\Email();
        $this->stack[] = new Entity\EnforcementConditionAppointment();
        $this->stack[] = new Entity\EnforcementDecisionCategory();
        $this->stack[] = new Entity\EnforcementDecisionOutcome();
        $this->stack[] = new Entity\EnforcementDecision();
        $this->stack[] = new Entity\EnforcementDecisionReinspectionOutcome();
        $this->stack[] = new Entity\EnforcementDecisionScore();
        $this->stack[] = new Entity\EnforcementFuelTypeApproval();
        $this->stack[] = new Entity\EnforcementFullPartialRetest();
        $this->stack[] = new Entity\EnforcementMotDemoTest();
        $this->stack[] = new Entity\EnforcementMotTestDifference();
        $this->stack[] = new Entity\EnforcementMotTestResult();
        $this->stack[] = new Entity\EnforcementSiteAssessment();
        $this->stack[] = new Entity\EnforcementVisitOutcomeApprovalLookup();
        $this->stack[] = new Entity\EnforcementVisitOutcome();
        $this->stack[] = new Entity\EquipmentMake();
        $this->stack[] = new Entity\EquipmentModel();
        $this->stack[] = new Entity\Equipment('serial-number-123');
        $this->stack[] = new Entity\EquipmentType();
        $this->stack[] = new Entity\Event();
        $this->stack[] = new Entity\Experience();
        $this->stack[] = new Entity\FacilityType();
        $this->stack[] = new Entity\FuelType();
        $this->stack[] = new Entity\Gender();
        $this->stack[] = new Entity\Licence();
        $this->stack[] = new Entity\LicenceType();
        $this->stack[] = new Entity\Make();
        $this->stack[] = new Entity\ModelDetail();
        $this->stack[] = new Entity\Model();
        $this->stack[] = new Entity\MotTest();
        $this->stack[] = new Entity\MotTestReasonForCancel();
        $this->stack[] = new Entity\MotTestReasonForRejection();
        $this->stack[] = new Entity\MotTestStatus();
        $this->stack[] = new Entity\NotificationActionLookup();
        $this->stack[] = new Entity\NotificationAction();
        $this->stack[] = new Entity\NotificationField();
        $this->stack[] = new Entity\Notification();
        $this->stack[] = new Entity\NotificationTemplateAction();
        $this->stack[] = new Entity\NotificationTemplate();
        $this->stack[] = new Entity\Organisation();
        $this->stack[] = new Entity\OrganisationType();
        $this->stack[] = new Entity\Payment();
        $this->stack[] = new Entity\Person();
        $this->stack[] = new Entity\Phone();
        $this->stack[] = new Entity\Qualification();
        $this->stack[] = new Entity\QualificationType();
        $this->stack[] = new Entity\ReasonForRefusal();
        $this->stack[] = new Entity\ReasonForRejectionType();
        $this->stack[] = new Entity\CertificateReplacementDraft();
        $this->stack[] = new Entity\SiteFacility();
        $this->stack[] = new Entity\SiteType();
        $this->stack[] = new Entity\SpecialNoticeContent();
        $this->stack[] = new Entity\SpecialNotice();
        $this->stack[] = new Entity\TestItemSelector();
        $this->stack[] = new Entity\ReasonForRejection();
        $this->stack[] = new Entity\TestSlotTransaction();
        $this->stack[] = new Entity\Title();
        $this->stack[] = new Entity\TransmissionType();
        $this->stack[] = new Entity\VehicleClass();
        $this->stack[] = new Entity\Vehicle();
        $this->stack[] = new Entity\VehicleTestingStationApplication();
        $this->stack[] = new Entity\VehicleTestingStationDetails();
        $this->stack[] = new Entity\VehicleTestingStationEvidenceOfExclusiveUse();
        $this->stack[] = new Entity\Site();
        $this->stack[] = new Entity\VehicleTestingStationPlanningPermission();
        $this->stack[] = new Entity\VehicleTestingStationPlansAndDimensions();
        $this->stack[] = new Entity\VehicleTestingStationSearch();
        $this->stack[] = new Entity\VehicleTestingStationTestingFacility();
        $this->stack[] = new Entity\VehicleTestingStationVehicleClass();
        $this->stack[] = new Entity\Visit();
        $this->stack[] = new Entity\VisitReason();
        $this->stack[] = new Entity\Address();
        $this->stack[] = new Entity\WheelplanType();
        $this->stack[] = new Entity\Event();
        $this->stack[] = new Entity\EventType();
        $this->stack[] = new Entity\EventTypeOutcomeCategoryMap();
        $this->stack[] = new Entity\EventOutcome();
        $this->stack[] = new Entity\EventPersonMap();
        $this->stack[] = new Entity\EventCategory();
        $this->stack[] = new Entity\EventOrganisationMap();
        $this->stack[] = new Entity\EventSiteMap();
        $this->stack[] = new Entity\TestSlotTransactionAmendmentReason();
    }

    public function testEntities()
    {
        $declaredClasses = get_declared_classes();
        foreach ($declaredClasses as $declaredClass) {
            if (preg_match('/^Dvsa/', $declaredClass)) {
                $split = explode('\\', $declaredClass);
                if (in_array('Entity', $split) && !preg_match('/Test/', $split[0])) {
                    $reflectionClass = new \ReflectionClass($declaredClass);

                    if ($reflectionClass->IsInstantiable()) {
                        $constructor = $reflectionClass->getConstructor();
                        $params = $constructor === null ? [] : $constructor->getParameters();

                        if (empty($params)) {
                            $class = new $declaredClass;
                            $methods = $reflectionClass->getMethods();

                            foreach ($methods as $method) {
                                $methodName = $method->getName();
                                $methodParams = $method->getParameters();

                                if (preg_match('/^set/', $methodName)) {
                                    if (count($methodParams) === 1 && $methodParams[0]->allowsNull()) {
                                        $this->assertTrue(true);
                                        $class->$methodName(null);
                                    }
                                }

                                if (preg_match('/^(get|is)/', $methodName)
                                    && empty($methodParams)
                                    && array_key_exists($methodName, $this->excludeMethods)
                                ) {
                                    $this->assertTrue(true);
                                    $class->$methodName();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
