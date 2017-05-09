<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Model\DvsaRole;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaDocument\Service\Document\DocumentService;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestService;
use DvsaReport\Service\Report\ReportService;
use Zend\Http\Header\Accept;

/**
 * Provide functionality for accessing early created PDF reports.
 */
class CertificatePrintingController extends AbstractDvsaRestfulController
{
    const MSG_NOT_FOUND = 'Requested MOT id not found';
    const MSG_NO_SERVICE = "%s was not located\n";

    const TYPE_HTML = 'text/html';
    const TYPE_PDF = 'application/pdf';

    const URL_DUPLICATE = 'dup';

    //  --  Jasper report parameters    --
    const JREPORT_PRM_WATERMARK = 'Watermark';
    const JREPORT_PRM_ISSUER = 'IssuerInfo';
    const JREPORT_PRM_SITE_NR = 'Vts';
    const JREPORT_PRM_INSP_AUTH = 'InspectionAuthority';

    const SNAPSHOT_DATA_KEY = 'snapshotData';

    const ISSUER_INFO_ENG = '%s certificate issued by %s on %s';
    const ISSUER_INFO_WEL = '%s wedi ei gyhoeddi gan %s ar %s';
    const ISSUER_DVSA_INFO_WEL = 'Anfonwyd %s gan %s ar %s';

    private static $validNames = ['CT20', 'CT30', 'CT32'];

    /** @var MotTestService */
    private $motTestService;

    /** @var DocumentService */
    private $documentService;

    /** @var ReportService */
    private $reportService;

    /** @var AbstractMotAuthorisationService */
    private $authorisationService;

    public function __construct(
        DocumentService $documentService,
        AbstractMotAuthorisationService $authorisationService
    ) {
        $this->documentService = $documentService;
        $this->authorisationService = $authorisationService;
    }

    public function printByDocIdAction()
    {
        if (!$this->requestIsPdfOrHtml()) {
            return ApiResponse::httpResponse(400);
        }

        $docId = (int) $this->params()->fromRoute('docId', null);
        $reportName = $this->documentService->getReportName($docId);

        if ($docId === 0 || empty($reportName)) {
            return ApiResponse::httpResponse(400);
        }

        //  --  return report data  --
        return $this->getReportService()->getReportById(
            $docId, $reportName, [
                self::JREPORT_PRM_WATERMARK => $this->getInvalidWatermark(),
                self::SNAPSHOT_DATA_KEY => $this->getSnapshotData($docId),
            ]
        );
    }

    /**
     * Prints a contingency form.
     *
     * @return \DvsaCommonApi\Model\ApiResponse|mixed
     */
    public function printContingencyAction()
    {
        if (!$this->requestIsPdfOrHtml()) {
            return ApiResponse::httpResponse(400);
        }

        $certificate = $this->params()->fromRoute('name', null);
        $testStation = $this->params()->fromQuery('testStation', null);
        $inspAuthority = $this->params()->fromQuery('inspAuthority', null);

        if (is_null($certificate) || !in_array($certificate, self::$validNames)
            || is_null($testStation)
            || is_null($inspAuthority)
        ) {
            return ApiResponse::httpResponse(400);
        }

        return $this->getReportService()->getReport(
            'MOT/'.$certificate.'.pdf',
            [
                self::JREPORT_PRM_SITE_NR => $testStation,
                self::JREPORT_PRM_INSP_AUTH => $inspAuthority,
                self::JREPORT_PRM_WATERMARK => $this->getInvalidWatermark(),
            ]
        );
    }

    /**
     * Extracts a PDF document. The given MOT test is loaded and then we
     * find the related Jasper document id, if present. If document doesn't exist
     * we try to populate database with proper data and print it.
     *
     * This uses pre-validated data and settings from the API handler to actually build a request
     * to Jasper and package up the returned PDF page(s) for the browser to receive.
     *
     * Printing a "duplicate" means that ONLY the pass or fail part an of MOT test result is to
     * be printed, not both pages. A "re-issue" and a "duplicate" are different things!
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function printAction()
    {
        $motTestNr = (int) $this->params('id', null);
        $isDuplicate = $this->params('dupmode', null);

        if (!$this->requestIsPdfOrHtml()) {
            return ApiResponse::httpResponse(400);
        }

        $this->getService('MotTestService', $this->motTestService);

        /** @var $motTest MotTestDto */
        $motTest = $this->motTestService->getMotTestData($motTestNr);

        /*
         * This is meant to fix a problem with migrated data.
         * During migration we haven't populated `jasper_document_variables` table.
         * Before we print out anything we must populate this table and update `mot_test`.`document_id`
         */
        $certificateService = $this->getCertificateCreationService();
        if ($motTest->getDocument() === null) {
            $motTest = $certificateService->createFromMotTestNumber(
                $motTest->getMotTestNumber(),
                $this->getIdentity()->getUserId()
            );
        }

        //  --  get mot test certificated   --
        $certificates = MotTestController::getCertificateDetailsContent(
            $motTest,
            null,
            $this->motTestService,
            $this->documentService
        );

        if (!count($certificates)) {
            return ApiResponse::httpResponse(404);
        }

        //  --  define additional parameters    --
        $firstCert = is_array($certificates) ? current($certificates) : [];
        $isFirstCertIsReplacement = (ArrayUtils::tryGet($firstCert, 'isReplacement', false) === true);

        $runtimeParameters = [
            self::JREPORT_PRM_WATERMARK => $this->getInvalidWatermark($motTest),
        ];

        if ($isFirstCertIsReplacement || $isDuplicate) {
            // @NOTE VM-2805 specifies that a duplicate always trumps replacement in
            // terms of the text printed on the cert
            $runtimeParameters += [
                self::JREPORT_PRM_ISSUER => $this->getIssuerInfo($isDuplicate, CertificateCreationService::isRequiresDualLanguage($motTest)),
            ];
        }

        //  --
        // For a reprint, we want EITHER the PASS OR the FAIL part of the test
        // to be reprinted from the user journey, not both parts so we ONLY
        // request that matching pass/fail part to be printed here.
        $reportArgs = [];

        $snapshotDocumentId = $motTest->getDocument();

        foreach ($certificates as $certificateDetail) {
            $certDocId = ArrayUtils::tryGet($certificateDetail, 'documentId');

            $isDuplicateDoc = ($isDuplicate && $snapshotDocumentId == $certDocId);
            if (!$isDuplicate || $isDuplicateDoc) {

                // Pull in snapshot data and pass it to the certificate creation call
                $snapshotData = $this->documentService->getSnapshotById($certDocId);

                if (empty($snapshotData)) {
                    throw new \LogicException('Unable to find certificate json snapshot data for certificate document id: '.$certDocId);
                }
                $runtimeParameters['snapshotData'] = $this->documentService->getSnapshotById($certDocId);
                $reportArgs[] = [
                    'documentId' => $certDocId,
                    'reportName' => $this->amendTemplateForContentType($certificateDetail['reportName']),
                    'runtimeParams' => $runtimeParameters,
                ];

                if ($isDuplicateDoc) {
                    break;
                }
            }
        }

        //  --  return report data  --
        return $this->getReportService()->getMergedPdfReports($reportArgs);
    }

    private function getIssuerInfo($isDuplicate, $isRequiresDualLanguage = false)
    {
        $person = $this->getIdentity()->getPerson();
        $roles = $this->authorisationService->getRolesAsArray($person->getId());

        $hasDvsaRole = false;
        foreach ($roles as $role) {
            if (DvsaRole::isDvsaRole($role)) {
                $hasDvsaRole = true;
                break;
            }
        }
        $advisoryInformation = $this->getEnglishVersion($isDuplicate);

        if ($isRequiresDualLanguage) {
            $advisoryInformation .= ' / '.$this->getWelshVersion($isDuplicate, $hasDvsaRole);
        }

        return $advisoryInformation;
    }

    private function getEnglishVersion($isDuplicate)
    {
        $type = 'Replacement';
        if ($isDuplicate) {
            $type = 'Duplicate';
        }

        $date = DateUtils::nowAsUserDateTime();
        $tester = $this->getIdentity()->getPerson()->getDisplayShortName();

        return sprintf(self::ISSUER_INFO_ENG, $type, $tester, $date->format('d F Y'));
    }

    private function getWelshVersion($isDuplicate, $hasDvsaRole = false)
    {
        $date = datefmt_format_object(
            DateUtils::nowAsUserDateTime(), 'dd MMMM Y', 'cy_GB'
        );
        $tester = $this->getIdentity()->getPerson()->getDisplayShortName();

        if ($hasDvsaRole && $isDuplicate) {
            return sprintf(self::ISSUER_DVSA_INFO_WEL, "copi o'r dystysgrif", $tester,  $date);
        } elseif ($hasDvsaRole) {
            return sprintf(self::ISSUER_DVSA_INFO_WEL, 'tystysgrif gyfnewid', $tester,  $date);
        }

        $type = 'Ailddodiad';
        if ($isDuplicate) {
            $type = 'Dyblyg';
        }

        return sprintf(self::ISSUER_INFO_WEL, $type, $tester, $date);
    }

    /**
     * Adjusts the reportname based on the Accept header of the request.
     *
     * @param $reportName
     *
     * @return string
     */
    private function amendTemplateForContentType($reportName)
    {
        if ($this->requestIs(self::TYPE_HTML)) {
            $reportName = str_replace('.pdf', '.html', $reportName);
        }

        return $reportName;
    }

    private function requestIsPdfOrHtml()
    {
        return $this->requestIs([self::TYPE_HTML, self::TYPE_PDF]);
    }

    /**
     * This will check that "accepts" header and ensure that only a single returned
     * document type is given. It MUST be "html" OR "pdf". Any other values will cause
     * a 400 bad request response to be generated.
     *
     * @param mixed $contentType Array or single contenttype
     *
     * @return bool
     */
    private function requestIs($contentType)
    {
        $valid = false;

        /**
         * @var \Zend\Http\Headers
         * @var \Zend\Http\Request $request
         */
        $request = $this->getRequest();
        $requestHeaders = $request->getHeaders();

        if ($requestHeaders->has('Accept')) {
            $accept = $requestHeaders->get('Accept');

            if ($accept instanceof Accept) {
                $type = $accept->getFieldValue();
                if (is_array($contentType)) {
                    $valid = in_array($type, $contentType);
                } else {
                    $valid = ($type == $contentType);
                }
            }
        }

        return $valid;
    }

    /**
     * Used to locate and return a service handle. If the required service name fails
     * to load we output a string to stdout.
     *
     * @param $name string the service we want to get a handle to
     * @param $into object reference for the loaded service handle
     *
     * @return array|null|object
     */
    private function getService($name, &$into)
    {
        $into = $this->getServiceLocator()->get($name);

        if ($into === null) {
            throw new \RuntimeException('Can not get instance of service '.$name);
        }

        return $this;
    }

    private function getReportService()
    {
        if ($this->reportService === null) {
            $this->getService('ReportService', $this->reportService);
        }

        return $this->reportService;
    }

    private function getInvalidWatermark(MotTestDto $motTest = null)
    {
        $config = $this->getServiceLocator()->get('config');
        $configPfd = ArrayUtils::tryGet($config, 'pdf');

        $text = trim(ArrayUtils::tryGet($configPfd, 'invalidWatermarkText'), '');

        $isPrint = (bool) ArrayUtils::tryGet($configPfd, 'invalidWatermark')
            && !(
                ($motTest instanceof MotTestDto)
                && ($motTest->getTestType()->getCode() === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING)
            );

        return $isPrint ? $text : '';
    }

    /**
     * @return CertificateCreationService
     */
    private function getCertificateCreationService()
    {
        return $this->getServiceLocator()->get(CertificateCreationService::class);
    }

    private function getSnapshotData($certDocId)
    {
        $document = $this->documentService->getSnapshotById($certDocId);

        return $document;
    }
}
