<?php

namespace DvsaMotApi\Model;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Constants\SiteAssessment;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Service\TesterService;
use DvsaMotApi\Service\UserService;
use OrganisationApi\Service\AuthorisedExaminerService;
use SiteApi\Service\SiteService;

/**
 * Class SiteAssessmentValidator.
 */
class SiteAssessmentValidator
{
    protected $dataFields;
    protected $data;
    protected $isValid;
    protected $errors;

    /**
     * @var SiteService
     */
    protected $svcSites;
    /**
     * @var DataCatalogService
     */
    protected $svcCatalog;

    /**
     * @var AuthorisedExaminerService
     */
    protected $svcAE;

    /**
     * @var TesterService
     */
    protected $svcTester;
    /**
     * @var UserService
     */
    protected $svcUser;

    /**! Validated site number minus the "- blah" part  */
    protected $siteNumber;

    const F_SITE_DETAILS = 'vts-search';
    const F_SITE_SCORE = 'site-score';
    const F_DAY = 'day';
    const F_MONTH = 'month';
    const F_YEAR = 'year';
    const F_AE_REP_ID = 'ae-rep-id';
    const F_AE_REP_NAME = 'ae-rep-name';
    const F_AE_REP_POSITION = 'ae-rep-pos';
    const F_VISIT_OUTCOME = 'visit-outcome';
    const F_TESTER_ID = 'tester-id';
    const F_ADVISORY_ISSUED = 'advisory-issued';
    const F_SITE_SEARCH_ID = 'searchSiteNumber';

    public static $fieldLabels
        = [
            self::F_SITE_DETAILS => 'Site number and name',
            self::F_VISIT_OUTCOME => 'Visit outcome',
            self::F_TESTER_ID => 'Tester ID',
            self::F_ADVISORY_ISSUED => 'Advisory issued',
            self::F_SITE_SCORE => 'Site score',
            self::F_AE_REP_ID => 'AE/representative ID',
            self::F_AE_REP_NAME => 'AE/representative name',
            self::F_AE_REP_POSITION => 'AE/representative position',
            self::F_DAY => 'Visit day',
            self::F_MONTH => 'Visit month',
            self::F_YEAR => 'Visit year',
        ];

    protected static $mandatoryFields
        = [
            self::F_SITE_DETAILS,
            self::F_VISIT_OUTCOME,
            self::F_TESTER_ID,
            self::F_ADVISORY_ISSUED,
            self::F_DAY,
            self::F_MONTH,
            self::F_YEAR,
            self::F_AE_REP_POSITION,
            self::F_SITE_SCORE,
        ];

    protected static $conditionalFields
        = [
            self::F_AE_REP_ID,
            self::F_AE_REP_NAME,
            self::F_SITE_SEARCH_ID,
        ];

    const VO_SATISFACTORY = 1;
    const VO_SHORTCOMINGS = 2;
    const VO_ABANDONED = 3;

    /**
     * Constructs an instance of the Site Assessment validation class.
     *
     * @param array       $data           the POST data from the form or other source
     * @param null        $siteService    service manager: Sites
     * @param null        $aeService      service manager: AuthorisedExaminer
     * @param null        $testerService  service manager: Tester
     * @param null        $catalogService service manager: API catalog data
     * @param UserService $userService    service manager: User information
     */
    public function __construct(
        array $data,
        $siteService = null,
        $aeService = null,
        $testerService = null,
        $catalogService = null,
        $userService = null
    ) {
        $this->svcSites = $siteService;
        $this->svcAE = $aeService;
        $this->svcTester = $testerService;
        $this->svcCatalog = $catalogService;
        $this->svcUser = $userService;

        $this->isValid = false;
        $this->errors = [];

        foreach ($data as $k => $v) {
            if (in_array($k, self::$mandatoryFields) || in_array($k, self::$conditionalFields)) {
                $this->data[$k] = $v;
            }
        }

        $this
            ->validateScore()
            ->validateSiteDetails()
            ->validateVisitOutcome()
            ->validateAdvisoryIssued()
            ->validateAeDetails()
            ->validateTesterDetails()
            ->validateVisitDate();
    }

    /**! Simple accessor to expose data */
    public function getData()
    {
        return $this->data;
    }

    /**! Simple accessor to get errors */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * The site details consist of the site number and the name, we want just the first
     * part of the string ("-" delimited) and also to confirm that the value given is
     * that of a real testing station.
     *
     * @return SiteAssessmentValidator
     *
     * @throws \Exception
     */
    protected function validateSiteDetails()
    {
        if ($this->mustHaveField(isset($this->data[self::F_SITE_DETAILS]), self::F_SITE_DETAILS)) {

            // This field is to be ignored if the field "siteSearchNumber" is present and has
            // a value other than '-1'; this indicates a JS powered search was completed and
            // that the internal site number is already available in that field.
            $id = null;

            if (isset($this->data[self::F_SITE_SEARCH_ID]) && ((int) -1 != $this->data[self::F_SITE_SEARCH_ID])) {
                $id = $this->data[self::F_SITE_SEARCH_ID];
            } else {
                if (1 === preg_match('/\w+/', $this->data[self::F_SITE_DETAILS], $match)) {
                    $id = $match[0];
                }
            }

            if (strlen($id)) {
                $this->data[self::F_SITE_DETAILS] = trim($id);

                if ($this->svcSites) {
                    try {
                        $site = $this->svcSites->getSiteBySiteNumber($id);
                    } catch (\Exception $e) {
                        $site = null;
                    }

                    $this->mustHave(
                        !empty($site),
                        self::F_SITE_DETAILS,
                        'Invalid site number: '.$id
                    );

                    if ($site && !empty($site->getSiteNumber())) {
                        $this->siteNumber = $site->getSiteNumber();
                    }
                }
            } else {
                $this->mustHaveField(false, self::F_SITE_DETAILS);
            }
        }

        return $this;
    }

    /**
     * Ensures the visit outcome is one of the approved values.
     *
     * @return SiteAssessmentValidator
     */
    protected function validateVisitOutcome()
    {
        if ($this->mustHaveField(isset($this->data[self::F_VISIT_OUTCOME]), self::F_VISIT_OUTCOME)) {
            $vo = $this->data[self::F_VISIT_OUTCOME];

            if (!is_null($this->svcCatalog)) {
                $outcomes = $this->svcCatalog->getSiteAssessmentVisitOutcomeData();
                $validOutcome = false;

                foreach ($outcomes as $each) {
                    if ($each['id'] == $vo) {
                        $validOutcome = true;
                        break;
                    }
                }
                $this->mustHave($validOutcome, self::F_VISIT_OUTCOME, 'Invalid visit outcome: '.$vo);
            }
        }

        return $this;
    }

    /**
     * Ensures the advisory issued setting is one of the approved values.
     *
     * @return SiteAssessmentValidator
     */
    protected function validateAdvisoryIssued()
    {
        if ($this->mustHaveField(isset($this->data[self::F_ADVISORY_ISSUED]), self::F_ADVISORY_ISSUED)) {
            $ai = $this->data[self::F_ADVISORY_ISSUED];

            switch ($ai) {
                case 'Y':
                case 'N':
                    break;
                default:
                    $this->mustHave(false, self::F_ADVISORY_ISSUED, 'Invalid advisory issued: '.$ai);
            }
        }

        return $this;
    }

    /**
     * Validate score value is not empty, digit and
     * between SiteAssessment::RISK_SCORE_MIN and SiteAssessment::RISK_SCORE_MAX.
     *
     * @see SiteAssessment
     *
     * @return SiteAssessmentValidator
     */
    protected function validateScore()
    {
        $visitOutcome = ArrayUtils::tryGet($this->data, self::F_VISIT_OUTCOME, null);
        if (!empty($visitOutcome) && (int) $visitOutcome === self::VO_ABANDONED) {
            return $this;
        }

        $score = $this->data[self::F_SITE_SCORE];
        $fieldName = self::$fieldLabels[self::F_SITE_SCORE];

        $this->mustHave(
            is_numeric($score) && (float) $score >= SiteAssessment::RISK_SCORE_MIN,
            $fieldName,
            'A site score must be a number and greater or equals zero'
        );
        $this->mustHave(
            (float) $score <= SiteAssessment::RISK_SCORE_MAX,
            $fieldName,
            'A site score cannot be higher than '.SiteAssessment::RISK_SCORE_MAX
        );

        return $this;
    }

    /**
     * Validate the AE details by testing to see if the username was given  in which case
     * it must exist, or if not given, that the name was specified instead.
     *
     * @return SiteAssessmentValidator
     */
    protected function validateAeDetails()
    {
        $aeId = isset($this->data[self::F_AE_REP_ID]) ? trim($this->data[self::F_AE_REP_ID]) : '';
        $aeName = isset($this->data[self::F_AE_REP_NAME]) ? trim($this->data[self::F_AE_REP_NAME]) : '';
        $aePos = isset($this->data[self::F_AE_REP_POSITION]) ? trim($this->data[self::F_AE_REP_POSITION]) : '';

        $this->mustHaveField((strlen($aeName) || strlen($aeId)), self::F_AE_REP_ID);
        $this->mustHaveField(strlen($aePos), self::F_AE_REP_POSITION);

        if (strlen($aeId)) {
            if (!is_null($this->svcAE)) {
                try {
                    $data = $this->svcAE->getAuthorisedExaminerData($aeId);
                } catch (\Exception $e) {
                    $data = null;
                }

                $this->mustHave(
                    !empty($data),
                    self::F_AE_REP_ID,
                    'Invalid AE/representative ID: '.$aeId
                );
            }
        }

        return $this;
    }

    /**
     * Ensure that the Tester ID value is present and has a non-zero length.
     * If given a non-zero length, that value MUST resolve to a real tester account.
     *
     * If the visit outcome is Abandoned we don't need tester ID.
     *
     * @assume visit outcome is already validated before coming here.
     *
     * @return \DvsaMotApi\Model\SiteAssessmentValidator
     */
    protected function validateTesterDetails()
    {
        if (isset($this->data[self::F_VISIT_OUTCOME])
            && self::VO_ABANDONED == $this->data[self::F_VISIT_OUTCOME]
        ) {
            return $this;
        }

        if ($this->mustHaveField(
            (isset($this->data[self::F_TESTER_ID])
                && strlen($this->data[self::F_TESTER_ID])), self::F_TESTER_ID
        )
        ) {
            if ($this->svcTester) {
                $testerId = $this->data[self::F_TESTER_ID];
                $userId = null;

                // lookup the user with name $testerId,
                if (!is_null($this->svcUser)) {
                    try {
                        $data = $this->svcUser->getUserData($testerId);
                    } catch (\Exception $e) {
                        $data = null;
                    }

                    if ($data) {
                        $userId = $data['id'];
                    }
                }

                if ($userId) {
                    try {
                        $data = $this->svcTester->getTesterByUserId($userId);
                    } catch (\Exception $e) {
                        $data = null;
                    }
                }
                $this->mustHave(
                    !empty($data),
                    self::F_TESTER_ID,
                    'Invalid Tester ID: '.$testerId
                );
            }
        }

        return $this;
    }

    /**
     * Ensures that the day, month and year values of the visit date are within the
     * expected values for D,M,Y and also that the days in the month is accurate for
     * the month with respect to days in month and leap year adjustments.
     *
     * @return SiteAssessmentValidator
     */
    protected function validateVisitDate()
    {
        $hasDay = $this->mustHaveField(isset($this->data['day']), 'day');
        $hasMonth = $this->mustHaveField(isset($this->data['month']), 'month');
        $hasYear = $this->mustHaveField(isset($this->data['year']), 'year');

        if ($hasDay && $hasMonth && $hasYear) {
            $day = $this->data['day'];
            $month = $this->data['month'];
            $year = $this->data['year'];

            if ($this->mustHave(
                (is_numeric($day) && $day >= 1 && $day <= 31),
                self::F_DAY,
                'Day must be in the range 1-31'
            )
            ) {
                $this->data['day'] = str_pad(2, '0', $day);
            }

            $this->mustHave(
                (is_numeric($month) && $month >= 1 && $month <= 12),
                self::F_MONTH,
                'Month must be in the range 1-12'
            );

            $this->mustHave(
                (is_numeric($year) && $year >= 1970),
                self::F_YEAR,
                'Year must be after 1970 and not in the future'
            );

            if (0 == count($this->errors)) {
                $dateOk = checkdate($month, $day, $year);
                $this->mustHave($dateOk, self::F_DAY, 'Incorrect days for the month/year');

                if ($dateOk) {
                    $userDate = DateUtils::toDateFromParts($day, $month, $year);

                    $this->mustHave(
                        false === DateUtils::isDateInFuture($userDate),
                        self::F_YEAR,
                        'Date cannot be in the future'
                    );
                }
            }
        }

        return $this;
    }

    /**
     * This checks that the $condition is true and if that is not the case then
     * it raises an exception containing the supplied message.
     *
     * @param $condition Bool must be true to not throw an exception
     * @param $fieldName String contains the internal field name to map to a label
     *
     * @return true or false
     *
     * @throws \Exception containing $message
     */
    protected function mustHaveField($condition, $fieldName)
    {
        return $this->mustHave($condition, $fieldName, 'Missing value: '.self::$fieldLabels[$fieldName]);
    }

    /**
     * This checks that the $condition is true and if that is not the case then
     * it raises an exception containing the supplied message.
     *
     * @param $condition Bool must be true to not throw an exception
     * @param $fieldName String contains an identifier to map back to the form input fields
     * @param $message   String containing the exception message (if required)
     *
     * @throws \Exception containing $message
     *
     * @return bool true if the condition was met
     */
    protected function mustHave($condition, $fieldName, $message)
    {
        if ($condition) {
            return true;
        }

        $error = new ErrorMessage(
            $message,
            BadRequestException::ERROR_CODE_INVALID_DATA,
            $message
        );

        $this->errors[$fieldName] = $error;

        return false;
    }

    public function getSiteNumber()
    {
        return $this->siteNumber;
    }
}
