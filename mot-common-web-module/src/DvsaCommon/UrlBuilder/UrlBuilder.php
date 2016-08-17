<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Class UrlBuilder
 *
 * @package DvsaCommon\UrlBuilder
 */
class UrlBuilder extends AbstractUrlBuilder
{
    const HOME = '';
    const IDENTITY_DATA = 'identity-data';
    const APPLICATION = 'application[/:id]';
    const AUTHORISED_EXAMINER_DESIGNATED_MANAGER = 'application/:uuid/designated-manager[/:aedmId]';
    const DATA_CATALOG = 'catalog';
    const ORGANISATION = 'application/:uuid/organisation';
    const CONTACT_DETAILS = 'application/:uuid/contact-details';
    const STATUS = 'application/:uuid/status';
    const PRINCIPAL = 'application/:uuid/principal[/:id]';
    const CONVICTION = 'application/:uuid/conviction[/:id]';
    const TESTER_APPLICATION = 'tester-application[/:uuid]';
    const TESTER_APPLICANT = '/tester-applicant/:testerId';
    const VEHICLE_TEST_CLASS = '/vehicle-test-class[/:id]';
    const EXAMINING_BODY = '/examining-body';
    const TESTER_APPLICATION_STATUS = '/status';
    const UPDATE_SECTION_STATE = '/update-section-state';
    const TESTER_APPLICATION_CONVICTION = '/conviction[/:id]';
    const EXPERIENCE = '/experience[/:id]';
    const QUALIFICATION = '/qualification[/:id]';
    const TESTER_ACCOUNT = 'tester-account';
    const REGISTRATION_COMPLETE = '/registration-complete/:id';
    const INDEX = '';
    const SESSION = 'session[/:id]';
    const USER = 'user[/:id]';
    const APPLICATIONS_FOR_USER = '/application';
    const SPECIAL_NOTICE = 'person/:id/special-notice[/:snId]';
    const SPECIAL_NOTICE_CONTENT = 'special-notice-content[/:id]';
    const SPECIAL_NOTICE_CONTENT_PUBLISH = 'special-notice-content/:id/publish';
    const SPECIAL_NOTICE_OVERDUE = "special-notice-overdue";
    const MOT_TEST = 'mot-test[/:motTestNumber]';
    const MOT_TEST_SHORT_SUMMARY = '/short-summary';
    const MOT_TEST_CERTIFICATE = 'mot-test-certificate';
    const MOT_CERTIFICATE_LIST = 'mot-recent-certificate[/:id]';
    const MOT_CERTIFICATE_EMAIL = 'mot-recent-certificate/:id/email';
    const MOT_PDF_DOWNLOAD = 'mot-recent-certificate/:motRecentCertificateId/pdf-link';
    const MOT_TEST_COMPARE_BY_ID = '/compare';
    const MOT_TEST_BRAKE_TEST_RESULT = '/brake-test-result';
    const MOT_TEST_BRAKE_TEST_VALIDATE_CONFIGURATION = '/validate-configuration';
    const MOT_TEST_OPTIONS = '/options';
    const TEST_ITEM_SELECTOR_LIST = '/test-item-selector';
    const TEST_ITEM_SELECTOR = '/test-item-selector/:tisId';
    // Search for reasons for rejection using the "search" parameter.
    const REASON_FOR_REJECTION = '/reason-for-rejection';
    const TEST_ITEM_CATEGORY_NAME = 'mot-test/:motTestNumber/test-item-category-name';
    const ODOMETER = '/odometer';
    const CERT_CHANGE_DIFF_TESTER_REASON = '/cert-change-diff-tester-reason';
    const ASSESSMENT_APPLICATION_COMMENT = 'assessment/application/:uuid/comment';
    const VEHICLE_TESTING_STATION = 'vehicle-testing-station[/:id]';
    const VEHICLE_TESTING_STATION_RISK_ASSESSMENT = '/risk-assessment';
    const APPLICATION_SECTION_STATE = 'application-section-state/:uuid';
    const APPLICATION_LOCK = 'application-lock/:uuid';
    const ACCOUNT = 'user-account';
    const PERSONAL_DETAILS = 'personal-details/:id';
    const PERSON = 'person/:id';
    const PERSON_CURRENT_MOT_TEST_NUMBER = '/current-mot-test';
    const PERSON_SITE_COUNT = '/site-count';
    const PERSON_MOT_TESTING = '/mot-testing';
    const TESTER_APPLICATION_LOCK = '/lock';
    const MOT_TEST_COMPARE = 'mot-test/compare';
    const REPLACEMENT_CERTIFICATE_DRAFT = '/replacement-certificate-draft[/:id]';
    const REPLACEMENT_CERTIFICATE_DRAFT_DIFF = '/diff';
    const REPLACEMENT_CERTIFICATE_DRAFT_APPLY = '/apply';
    const REPORT_NAME = 'get-report-name/:id';
    const EQUIPMENT = 'equipment-model';
    const EVENT = 'event';
    const INSPECTION_LOCATION_API = 'inspection-location';
    const CERTIFICATE_DETAILS = 'mot-test/:motTestNumber/certificate-details[/:variation]';
    const CREATE_DOCUMENT = 'create-document';
    const DELETE_DOCUMENT = 'delete-document/:id';
    const ENFORCEMENT_HOME = 'enforcement-home';
    const REINSPECTION_OUTCOME = 'reinspection-outcome';
    const ENFORCEMENT_MOT_TEST_RESULT = 'enforcement-mot-test-result[/:id]';
    const SITE_OPENING_HOURS = "/opening-hours";
    const VEHICLE_DICTIONARY = 'vehicle-dictionary';
    const MAKE = '/make[/:id]';
    const MODEL = '/model[/:id]';
    const MODELS = '/models';
    const MODEL_DETAILS = '/model-details';
    const CONTINGENCY = 'emergency-log';
    const CLAIM_ACCOUNT = 'account/claim/:userId';
    const SECURITY_QUESTION = 'security-question';
    const RESET_PIN = 'person/:userId/reset-pin';
    const SITE = 'site/:id';
    const AUTHORISED_CLASSES = '/authorised-classes';
    const DEMO_TEST_ASSESSMENT = 'person/:personId/demo-test-assessment';

    protected $routesStructure
        = [
            self::HOME                                   => '',
            self::IDENTITY_DATA                           => '',
            self::DEMO_TEST_ASSESSMENT                   => '',
            self::ENFORCEMENT_HOME                       => '',
            self::DATA_CATALOG                           => '',
            self::APPLICATION                            => '',
            self::AUTHORISED_EXAMINER_DESIGNATED_MANAGER => '',
            self::ORGANISATION                           => '',
            self::CONTACT_DETAILS                        => '',
            self::STATUS                                 => '',
            self::PRINCIPAL                              => '',
            self::CONVICTION                             => '',
            self::TESTER_APPLICATION                     =>
                [
                    self::TESTER_APPLICANT              => '',
                    self::VEHICLE_TEST_CLASS            => '',
                    self::EXAMINING_BODY                => '',
                    self::TESTER_APPLICATION_STATUS     => '',
                    self::UPDATE_SECTION_STATE          => '',
                    self::TESTER_APPLICATION_CONVICTION => '',
                    self::EXPERIENCE                    => '',
                    self::QUALIFICATION                 => '',
                    self::TESTER_APPLICATION_LOCK       => '',
                ],
            self::TESTER_ACCOUNT                         =>
                [
                    self::REGISTRATION_COMPLETE => '',
                ],
            self::INDEX                                  => '',
            self::SESSION                                => '',
            self::USER                                   =>
                [
                    self::APPLICATIONS_FOR_USER => '',
                ],
            self::SPECIAL_NOTICE                         => '',
            self::SPECIAL_NOTICE_CONTENT                 => '',
            self::SPECIAL_NOTICE_CONTENT_PUBLISH         => '',
            self::SPECIAL_NOTICE_OVERDUE                 => '',
            self::MOT_TEST_CERTIFICATE                   => '',
            self::MOT_CERTIFICATE_LIST                   => '',
            self::MOT_CERTIFICATE_EMAIL                  => '',
            self::MOT_PDF_DOWNLOAD                      =>  '',
            self::MOT_TEST                               => [
                self::TEST_ITEM_SELECTOR_LIST       => '',
                self::TEST_ITEM_SELECTOR            => '',
                self::REASON_FOR_REJECTION          => '',
                self::MOT_TEST_SHORT_SUMMARY        => '',
                self::MOT_TEST_BRAKE_TEST_RESULT    => [
                    self::MOT_TEST_BRAKE_TEST_VALIDATE_CONFIGURATION => ''
                ],
                self::ODOMETER                      => '',
                self::MOT_TEST_COMPARE_BY_ID        => '',
                self::MOT_TEST_OPTIONS              => '',
                self::REPLACEMENT_CERTIFICATE_DRAFT => [
                    self::REPLACEMENT_CERTIFICATE_DRAFT_APPLY => '',
                    self::REPLACEMENT_CERTIFICATE_DRAFT_DIFF  => '',
                ],
            ],
            self::CERT_CHANGE_DIFF_TESTER_REASON         => '',
            self::ASSESSMENT_APPLICATION_COMMENT         => '',
            self::VEHICLE_TESTING_STATION                => [
                self::SITE_OPENING_HOURS => '',
                self::AUTHORISED_CLASSES => '',
                self::VEHICLE_TESTING_STATION_RISK_ASSESSMENT => ''
            ],
            self::APPLICATION_SECTION_STATE              => '',
            self::APPLICATION_LOCK                       => '',
            self::ACCOUNT                                => '',
            self::PERSONAL_DETAILS                       => '',
            self::PERSON                                 => [
                self::PERSON_CURRENT_MOT_TEST_NUMBER     => '',
                self::PERSON_SITE_COUNT                  => '',
                self::PERSON_MOT_TESTING                 => '',
            ],
            self::MOT_TEST_COMPARE                       => '',
            self::EQUIPMENT                              => '',
            self::EVENT                                  => '',
            self::INSPECTION_LOCATION_API                => '',
            self::REINSPECTION_OUTCOME                   => '',
            self::CERTIFICATE_DETAILS                    => '',
            self::TEST_ITEM_CATEGORY_NAME                => '',
            self::ENFORCEMENT_MOT_TEST_RESULT            => '',
            self::CONTINGENCY                            => '',
            self::VEHICLE_DICTIONARY                     => [
                self::MAKE => [
                    self::MODEL => [
                        self::MODEL_DETAILS => ''
                    ],
                    self::MODELS => '',
                ],
            ],
            self::CLAIM_ACCOUNT => '',
            self::SECURITY_QUESTION => '',
            self::RESET_PIN => '',
        ];

    public function home()
    {
        return $this->appendRoutesAndParams(self::HOME);
    }

    public function enforcementHome()
    {
        return $this->appendRoutesAndParams(self::ENFORCEMENT_HOME);
    }

    public function dataCatalog()
    {
        return $this->appendRoutesAndParams(self::DATA_CATALOG);
    }

    public function application()
    {
        return $this->appendRoutesAndParams(self::APPLICATION);
    }

    public function authorisedExaminerDesignatedManager()
    {
        return $this->appendRoutesAndParams(self::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);
    }

    public function organisation()
    {
        return $this->appendRoutesAndParams(self::ORGANISATION);
    }

    public function contactDetails()
    {
        return $this->appendRoutesAndParams(self::CONTACT_DETAILS);
    }

    public function status()
    {
        return $this->appendRoutesAndParams(self::STATUS);
    }

    public function principal()
    {
        return $this->appendRoutesAndParams(self::PRINCIPAL);
    }

    public function conviction()
    {
        return $this->appendRoutesAndParams(self::CONVICTION);
    }

    public function testerApplication()
    {
        return $this->appendRoutesAndParams(self::TESTER_APPLICATION);
    }

    public function updateSectionState()
    {
        return $this->appendRoutesAndParams(self::UPDATE_SECTION_STATE);
    }

    public function testerApplicationConviction()
    {
        return $this->appendRoutesAndParams(self::TESTER_APPLICATION_CONVICTION);
    }

    public function experience()
    {
        return $this->appendRoutesAndParams(self::EXPERIENCE);
    }

    public function qualification()
    {
        return $this->appendRoutesAndParams(self::QUALIFICATION);
    }

    public function testerApplicant()
    {
        return $this->appendRoutesAndParams(self::TESTER_APPLICANT);
    }

    public function vehicleTestClass()
    {
        return $this->appendRoutesAndParams(self::VEHICLE_TEST_CLASS);
    }

    public function examiningBody()
    {
        return $this->appendRoutesAndParams(self::EXAMINING_BODY);
    }

    public function testerApplicationStatus()
    {
        return $this->appendRoutesAndParams(self::TESTER_APPLICATION_STATUS);
    }

    public function testerApplicationLock()
    {
        return $this->appendRoutesAndParams(self::TESTER_APPLICATION_LOCK);
    }

    public function testerAccount()
    {
        return $this->appendRoutesAndParams(self::TESTER_ACCOUNT);
    }

    public function registrationComplete()
    {
        return $this->appendRoutesAndParams(self::REGISTRATION_COMPLETE);
    }

    public function index()
    {
        return $this->appendRoutesAndParams(self::INDEX);
    }

    public function session()
    {
        return $this->appendRoutesAndParams(self::SESSION);
    }

    public static function user($id)
    {
        $urlBuilder = new self;

        return $urlBuilder->appendRoutesAndParams(self::USER)->routeParam('id', $id);
    }

    public function specialNotice()
    {
        return $this->appendRoutesAndParams(self::SPECIAL_NOTICE);
    }

    public function specialNoticeContent()
    {
        return $this->appendRoutesAndParams(self::SPECIAL_NOTICE_CONTENT);
    }

    public function specialNoticeContentPublish()
    {
        return $this->appendRoutesAndParams(self::SPECIAL_NOTICE_CONTENT_PUBLISH);
    }

    public function specialNoticeOverdue()
    {
        return $this->appendRoutesAndParams(self::SPECIAL_NOTICE_OVERDUE);
    }

    /**
     * @return $this
     * @depricated use MotTestUrlBuilder::motTest($testNumber)
     */
    public function motTest()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST);
    }

    public function motTestShortSummary()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST_SHORT_SUMMARY);
    }

    public function motTestCertificate()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST_CERTIFICATE);
    }

    public function motTestCertificates($vtsId)
    {
        return $this->appendRoutesAndParams(self::MOT_CERTIFICATE_LIST)
            ->queryParam('vtsId', $vtsId);
    }

    public function motRecentCertificate($id)
    {
        return $this->appendRoutesAndParams(self::MOT_CERTIFICATE_LIST)
            ->routeParam('id', $id);
    }

    public function motRecentCertificateEmail($id)
    {
        return $this->appendRoutesAndParams(self::MOT_CERTIFICATE_EMAIL)
            ->routeParam('id', $id);
    }

    public function motPdfDownloadLink($motRecentCertificateId)
    {
        return $this->appendRoutesAndParams(self::MOT_PDF_DOWNLOAD)
            ->routeParam('motRecentCertificateId', $motRecentCertificateId);
    }

    public function motTestCompareById()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST_COMPARE_BY_ID);
    }

    public function testItemSelectorList()
    {
        return $this->appendRoutesAndParams(self::TEST_ITEM_SELECTOR_LIST);
    }

    public function testItemSelector()
    {
        return $this->appendRoutesAndParams(self::TEST_ITEM_SELECTOR);
    }

    public function reasonForRejection()
    {
        return $this->appendRoutesAndParams(self::REASON_FOR_REJECTION);
    }

    public function brakeTestResult()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST_BRAKE_TEST_RESULT);
    }

    public function validateConfiguration()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST_BRAKE_TEST_VALIDATE_CONFIGURATION);
    }

    public function odometer()
    {
        return $this->appendRoutesAndParams(self::ODOMETER);
    }

    public function demoTestAssessment($personId)
    {
        return $this->appendRoutesAndParams(self::DEMO_TEST_ASSESSMENT)
            ->routeParam('personId', $personId);
    }

    public function certChangeDiffTesterReason()
    {
        $this->appendRoutesAndParams(self::CERT_CHANGE_DIFF_TESTER_REASON);
    }

    /** @deprecated use AuthorisedExaminerUrlBuilder::of($id) */
    public static function authorisedExaminer()
    {
        return new AuthorisedExaminerUrlBuilder();
    }

    public function assessmentApplicationComment()
    {
        return $this->appendRoutesAndParams(self::ASSESSMENT_APPLICATION_COMMENT);
    }

    public function vehicleTestingStation()
    {
        return $this->appendRoutesAndParams(self::VEHICLE_TESTING_STATION);
    }

    public static function applicationSectionState()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::APPLICATION_SECTION_STATE);
    }

    public static function applicationLock()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::APPLICATION_LOCK);
    }

    public static function account()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::ACCOUNT);
    }

    public static function personalDetails($id)
    {
        return UrlBuilder::of()->appendRoutesAndParams(self::PERSONAL_DETAILS)
            ->routeParam('id', $id);
    }

    /** @deprecated use PersonUrlBuilder */
    public static function person($personId)
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::PERSON)->routeParam('id', $personId);
    }

    public function currentMotTest()
    {
        return $this->appendRoutesAndParams(self::PERSON_CURRENT_MOT_TEST_NUMBER);
    }

    /**
     * @return $this
     */
    public function getSiteCount()
    {
        return $this->appendRoutesAndParams(self::PERSON_SITE_COUNT);
    }

    public function getMotTesting()
    {
        return $this->appendRoutesAndParams(self::PERSON_MOT_TESTING);
    }

    public function applicationsForUser()
    {
        return $this->appendRoutesAndParams(self::APPLICATIONS_FOR_USER);
    }

    //  @ARCHIVE VM-4532    function enforcementMotDemoTest()
    //  @ARCHIVE VM-4532    function enforcementMotDemoTestSubmit()

    public static function replacementCertificateDraft($id = null, $motTestNumber = null)
    {
        return self::of()->appendRoutesAndParams(self::MOT_TEST)
            ->routeParam('motTestNumber', $motTestNumber)
            ->appendRoutesAndParams(self::REPLACEMENT_CERTIFICATE_DRAFT)
            ->routeParam('id', $id);
    }

    public static function replacementCertificateDraftApply($id, $motTestNumber)
    {
        return self::replacementCertificateDraft($id, $motTestNumber)
            ->appendRoutesAndParams(self::REPLACEMENT_CERTIFICATE_DRAFT_APPLY);
    }

    public static function replacementCertificateDraftDiff($id, $motTestNumber)
    {
        return self::replacementCertificateDraft($id, $motTestNumber)
            ->appendRoutesAndParams(self::REPLACEMENT_CERTIFICATE_DRAFT_DIFF);
    }

    public function compareMotTest()
    {
        return $this->appendRoutesAndParams(self::MOT_TEST_COMPARE);
    }

    public function inspectionLocationApi()
    {
        return $this->appendRoutesAndParams(self::INSPECTION_LOCATION_API);
    }

    public function reinspectionOutcome()
    {
        return $this->appendRoutesAndParams(self::REINSPECTION_OUTCOME);
    }

    public static function equipmentModel()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::EQUIPMENT);
    }

    public static function event()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::EVENT);
    }

    public function certificateDetails()
    {
        return $this->appendRoutesAndParams(self::CERTIFICATE_DETAILS);
    }

    public static function siteAssessment()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::ENFORCEMENT_SITE_ASSESSMENT);
    }

    public static function contingency()
    {
        $urlBuilder = new UrlBuilder();

        return $urlBuilder->appendRoutesAndParams(self::CONTINGENCY);
    }

    public function testItemCategoryName()
    {
        return $this->appendRoutesAndParams(self::TEST_ITEM_CATEGORY_NAME);
    }

    public static function enforcementMotTestResult($enforcementMotTestResultId = null)
    {
        $urlBuilder = self::create()->appendRoutesAndParams(self::ENFORCEMENT_MOT_TEST_RESULT);

        if ($enforcementMotTestResultId !== null) {
            $urlBuilder->routeParam('id', $enforcementMotTestResultId);
        }

        return $urlBuilder;
    }

    public function siteOpeningHours()
    {
        return $this->appendRoutesAndParams(self::SITE_OPENING_HOURS);
    }

    public function siteRiskAssessment()
    {
        return $this->appendRoutesAndParams(self::VEHICLE_TESTING_STATION_RISK_ASSESSMENT);
    }

    public static function vehicleDictionary()
    {
        return (new UrlBuilder())->appendRoutesAndParams(self::VEHICLE_DICTIONARY);
    }

    public function make($id = null)
    {
        if (null === $id) {
            return $this->appendRoutesAndParams(self::MAKE);
        }
        return $this->appendRoutesAndParams(self::MAKE)->routeParam('id', $id);
    }

    public function model($id = null)
    {
        if (null === $id) {
            return $this->appendRoutesAndParams(self::MODEL);
        }
        return $this->appendRoutesAndParams(self::MODEL)->routeParam('id', $id);
    }

    public function models($id = null)
    {
        if (null === $id) {
            return $this->appendRoutesAndParams(self::MODELS);
        }
        return $this->appendRoutesAndParams(self::MODELS)->routeParam('id', $id);
    }

    public function modelDetails()
    {
        return $this->appendRoutesAndParams(self::MODEL_DETAILS);
    }

    public static function motTestOptions($motTestNumber)
    {
        return (new UrlBuilder())
            ->appendRoutesAndParams(self::MOT_TEST)
            ->routeParam('motTestNumber', $motTestNumber)
            ->appendRoutesAndParams(self::MOT_TEST_OPTIONS);
    }

    public function identityData()
    {
        return $this->appendRoutesAndParams(self::IDENTITY_DATA);
    }

    public static function claimAccount($userId)
    {
        return (new UrlBuilder())
            ->appendRoutesAndParams(self::CLAIM_ACCOUNT)
            ->routeParam('userId', $userId);
    }

    public function securityQuestion()
    {
        return $this->appendRoutesAndParams(self::SECURITY_QUESTION);
    }

    public function resetPin($userId)
    {
        return $this->appendRoutesAndParams(self::RESET_PIN)
                    ->routeParam('userId', $userId);
    }

    public static function site($id)
    {
        return self::of()->appendRoutesAndParams(self::SITE)
            ->routeParam('id', $id);
    }

    public function getAuthorisedClasses()
    {
        return $this->appendRoutesAndParams(self::AUTHORISED_CLASSES);
    }
}
