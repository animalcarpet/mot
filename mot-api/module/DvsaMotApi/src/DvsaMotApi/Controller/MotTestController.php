<?php
namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaDocument\Service\Document\DocumentService;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\VehicleHistoryService;
use SiteApi\Service\SiteService;
use Zend\View\Model\JsonModel;

/**
 * Class MotTestController.
 */
class MotTestController extends AbstractDvsaRestfulController implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    const FIELD_SITEID = 'siteid';
    const FIELD_LOCATION = 'location';
    const FIELD_ONE_PERSON_TEST = 'onePersonTest';
    const FIELD_ONE_PERSON_RE_INSPECTION = 'onePersonReInspection';

    const SITE_NUMBER_QUERY_PARAMETER = 'siteNumber';
    const VEHICLE_ID_QUERY_PARAMETER = 'vehicleId';
    const SITE_NUMBER_REQUIRED_MESSAGE = 'Query parameter siteNumber is required';
    const SITE_NUMBER_REQUIRED_DISPLAY_MESSAGE = 'Missing site number. Please enter a site number to search for';
    const ERROR_MSG_FAILED_TO_UPDATE_TEST = 'Failed to update the location for the site';
    const ERROR_MSG_EITHER_OR_LOCATION = 'Please supply a site ID or a location, not both';
    const ERROR_MSG_SITE_NUMBER_INVALID = 'Site number invalid';
    const ERROR_UNABLE_TO_PERFORM_SEARCH_WITH_PARAMS = 'Unable to perform search with given parameters';

    /**
     * MotTestController constructor.
     */
    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    public function get($motTestNumber)
    {
        $motTestData = $this->getMotTestService()->getMotTestData($motTestNumber);

        return ApiResponse::jsonOk($motTestData);
    }

    public function editAllowedCheckAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $vehicleId = $this->params()->fromRoute('vehicleId');
        $personId = $this->getUserId();

        $motTestEditAllowedDto = $this->getVehicleHistoryService()->getEditAllowedPermissionsDto($vehicleId, $personId, $motTestNumber);

        return $this->returnDto($motTestEditAllowedDto);
    }

    /**
     * @return JsonModel
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function getMinimalMotAction()
    {
        $motTestNumber    = $this->params()->fromRoute('motTestNumber');
        $motTestData = $this->getMotTestService()->getMotTestData($motTestNumber, true);

        return ApiResponse::jsonOk($motTestData);
    }

    public function create($data)
    {
        /** @var MotTest $motTest */
        $motTest = $this->getMotTestService()->createMotTest($data);

        return ApiResponse::jsonOk([
            "motTestNumber" => $motTest->getNumber(),
            "dvsaVehicleId" => $motTest->getVehicle()->getId()
        ]);
    }

    /**
     * Perform an UPDATE of an existing MOT record by checking for:
     *
     * siteid/location: mutually exclusive
     *
     * @param $motTestNumber int contains the re-inspection database row id
     * @param $data  Array the PUT data from the re-inspection summary form
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function update($motTestNumber, $data)
    {
        $locationSiteText = null;
        $locationSiteId = null;
        $onePersonTest = null;
        $onePersonReInspection = null;
        $errors = [];

        $motTest = $this->getMotTestService()->getMotTest($motTestNumber);

        if ($motTest && ($motTest->getMotTestType()->getIsReinspection() || $motTest->getMotTestType()->isNonMotTest())) {
            if (isset($data['operation']) && ($data['operation'] == 'updateOnePersonTest')) {
                list($onePersonTest, $onePersonReInspection)
                    = $this->validateOnePersonTest($motTestNumber, $data, $errors);

                if (count($errors) > 0) {
                    return $this->returnBadRequestResponseModelWithErrors($errors);
                }

                if ($onePersonTest && $onePersonReInspection) {
                    $onePersonTestUpdated = $this->getMotTestService()
                        ->updateOnePersonTest($motTestNumber, $onePersonTest, $onePersonReInspection);

                    if ($onePersonTestUpdated) {
                        return ApiResponse::jsonOk(['updated' => true]);
                    }
                }
            }

            if (isset($data['operation']) && ($data['operation'] == 'updateSiteLocation')) {
                list($locationSiteId, $locationSiteText)
                    = $this->performOffsiteValidation($motTestNumber, $data, $errors);

                if (count($errors) > 0) {
                    return $this->returnBadRequestResponseModelWithErrors($errors);
                }

                $locationUpdated = $this->getMotTestService()->updateMotTestLocation(
                    $this->getUsername(),
                    $motTestNumber,
                    $locationSiteId,
                    $locationSiteText
                );

                if (!$locationUpdated) {
                    return $this->returnBadRequestResponseModelWithErrors(
                        [
                            $this->makeErrorMessage(
                                self::ERROR_MSG_FAILED_TO_UPDATE_TEST,
                                self::ERROR_CODE_REQUIRED,
                                self::ERROR_MSG_FAILED_TO_UPDATE_TEST
                            )
                        ]
                    );
                }
                return ApiResponse::jsonOk(['updated' => true]);
            }
        }

        return null;
    }

    /**
     * If a reinspection has been specified then we need to ensure that a location has been
     * specified.
     *
     * @param $motId  String the MOT id from the URL parameter
     * @param $data   Array  the POST data sent from the page
     * @param $errors Array& accumulator for errors
     * @return array [new site id, new site location text]
     *
     * @throws BadRequestException
     *
     * @see http://objitsu.blogspot.co.uk/2013/01/php-and-lisp-multiple-value-bind-mvb.html
     */
    protected function performOffsiteValidation($motId, $data, &$errors)
    {
        $response = [null, null];
        $motTest = $this->getMotTestService()->getMotTest($motId);

        if (
            $motTest &&
            ($motTest->getMotTestType()->getIsReinspection() || $motTest->getMotTestType()->isNonMotTest())
        ) {
            $siteIdExists = array_key_exists(self::FIELD_SITEID, $data) && !empty($data[self::FIELD_SITEID]);
            $locationExists = array_key_exists(self::FIELD_LOCATION, $data) && !empty($data[self::FIELD_LOCATION]);

            if ($motTest->getMotTestType()->isNonMotTest() && !$siteIdExists) {
                throw new BadRequestException(
                    'Site ID - enter the site ID',
                    BadRequestException::ERROR_CODE_INVALID_DATA,
                    'enter the site ID'
                );
            }

            if (!$siteIdExists && !$locationExists) {
                $errors[] = $this->makeFieldIsRequiredError(self::FIELD_SITEID);
                return $response;
            }

            if ($siteIdExists && $locationExists) {
                $errors[] = $this->makeErrorMessage(self::ERROR_MSG_EITHER_OR_LOCATION);
                return $response;
            }

            if ($siteIdExists) {
                if (1 === preg_match('/\w+/', $data[self::FIELD_SITEID], $match)) {
                    $submittedId = $match[0];
                    try {
                        $site = $this->getVehicleTestingStationService()
                            ->getSiteBySiteNumber($submittedId);

                        $response = [$site->getId(), null];
                    } catch (\Exception $e) {
                        $errors[] = $this->makeErrorMessage(
                            self::ERROR_MSG_SITE_NUMBER_INVALID,
                            self::ERROR_CODE_REQUIRED,
                            self::ERROR_MSG_SITE_NUMBER_INVALID
                        );
                    }
                } else {
                    $errors[] = $this->makeErrorMessage(
                        self::ERROR_MSG_SITE_NUMBER_INVALID,
                        self::ERROR_CODE_REQUIRED,
                        self::ERROR_MSG_SITE_NUMBER_INVALID
                    );
                }
            } else {
                $response = [null, trim($data[self::FIELD_LOCATION])];
            }
        }
        return $response;
    }

    /**
     * Validate One Person Test Fields
     *
     * @param string $motId the MOT id from the URL parameter
     * @param array  $data the POST data sent from the page
     * @param array  $errors accumulator for errors
     *
     * @return array response
     */
    protected function validateOnePersonTest($motId, $data, &$errors)
    {
        $response = [null, null];
        $motTest = $this->getMotTestService()->getMotTest($motId);
        if ($motTest && ($motTest->getMotTestType()->getIsReinspection())) {
            $onePersonTest = array_key_exists(self::FIELD_ONE_PERSON_TEST, $data)
                && !empty($data[self::FIELD_ONE_PERSON_TEST]);

            $onePersonReInspection = array_key_exists(self::FIELD_ONE_PERSON_RE_INSPECTION, $data)
                && !empty($data[self::FIELD_ONE_PERSON_RE_INSPECTION]);

            if (!$onePersonTest) {
                $errors[] = $this->makeFieldIsRequiredError(self::FIELD_ONE_PERSON_TEST);
                return $response;
            }

            if (!$onePersonReInspection) {
                $errors[] = $this->makeFieldIsRequiredError(self::FIELD_ONE_PERSON_RE_INSPECTION);
                return $response;
            }

            return [$data[self::FIELD_ONE_PERSON_TEST], $data[self::FIELD_ONE_PERSON_RE_INSPECTION]];
        }
        return $response;
    }

    /**
     * Answers the actual string value of the given MOT test type field.
     *
     * @param MotTest $motTest
     *
     * @return string Internal MOT test type code from MotTestType
     */
    protected function getMotTestType($motTest)
    {
        $type = null;

        if ($motTest) {
            $testType = $motTest->getMotTestType();

            if ($testType) {
                $type = $testType->getCode();
            }
        }
        return $type;
    }

    /**
     * Based on MOT test certificate number returns common MOT test data
     *
     * @return JsonModel
     */
    public function getMotTestByNumberAction()
    {
        $number = $this->params()->fromQuery("number");

        return $this->get($number);
    }

    /**
     * Based on MOT test ID and optional variation, return the relevant
     * document identifiers and certificate names (VT20, VT30 etc)
     *
     * @return JsonModel
     */
    public function getCertificateDetailsAction()
    {
        $motTestNumber    = $this->params()->fromRoute('motTestNumber');
        $variation = $this->params()->fromRoute('variation', null);

        $motTest = $this->getMotTestService()->getMotTestData($motTestNumber);

        $content = self::getCertificateDetailsContent(
            $motTest,
            $variation,
            $this->getMotTestService(),
            $this->getDocumentService()
        );

        return ApiResponse::jsonOk($content);
    }

    /**
     * This will obtain the Jasper document id(s) of the relevant paperwork for the given test
     * and also obtain the relevant Jasper document template type for making the request to
     * Jasper. We also need to indicate if the certificate is a replacement or the original.
     *
     * @param MotTestDto      $motTest
     * @param string          $variation
     * @param MotTestService  $motService
     * @param DocumentService $documentService
     *
     * @return array
     */
    public static function getCertificateDetailsContent(
        MotTestDto $motTest,
        $variation,
        MotTestService $motService,
        DocumentService $documentService
    ) {
        $testId = $motTest->getId();

        $details = [];
        $certificateIds = $motService->getCertificateIds($motTest);

        if (count($certificateIds)) {
            // Check whether this is a replacement certificate or not
            $isReplacement = ($motService->getReplacementCertificate($testId) !== null);

            foreach ($certificateIds as $id) {
                $reportName = $documentService->getReportName($id, $variation);

                $details[] = [
                    'documentId'    => $id,
                    'reportName'    => $reportName,
                    'isReplacement' => $isReplacement
                ];
            }
        }

        return $details;
    }

    /**
     * Returns whether or not there is an existing in progress test for a vehicle id.
     * Expected params:
     *  route: id - vehicle id
     *
     * TODO extract to mot test - vehicle part
     *
     * @return JsonModel
     */
    public function isTestInProgressAction()
    {
        $vehicleId = $this->params()->fromRoute('id');
        $isPresent = $this->getMotTestService()->isTestInProgressForVehicle($vehicleId);

        return ApiResponse::jsonOk($isPresent);
    }

    /**
    * @return JsonModel
    */
    public function validateMOTRetestAction()
    {
        $motNumber = $this->params()->fromRoute('motTestNumber');
        $motTestData = $this->getMotTestService()->getMotTestDataForRetest($motNumber, true);

        return ApiResponse::jsonOk($motTestData);
    }

    /**
     * @return MotTestService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get('MotTestService');
    }

    /**
     * @return DocumentService
     */
    private function getDocumentService()
    {
        return $this->getServiceLocator()->get('DocumentService');
    }

    /**
     * @return SiteService
     */
    private function getVehicleTestingStationService()
    {
        return $this->getServiceLocator()->get(SiteService::class);
    }

    /**
     * @return VehicleHistoryService
     */
    private function getVehicleHistoryService()
    {
        return $this->getServiceLocator()->get(VehicleHistoryService::class);
    }
}
