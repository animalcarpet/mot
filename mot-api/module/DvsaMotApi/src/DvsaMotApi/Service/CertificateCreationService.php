<?php

namespace DvsaMotApi\Service;

use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaDocument\Service\Document\DocumentService;
use DvsaMotApi\Mapper;
use DvsaMotApi\Mapper\AbstractMotTestMapper;
use InvalidArgumentException;
use Zend\Stdlib\Hydrator\ClassMethods;

/**
 * Class CertificateCreationService
 *
 * @package DvsaMotApi\Service
 */
class CertificateCreationService
{
    /** @var MotTestService */
    private $motTestService;
    /** @var DocumentService  */
    private $documentService;
    /** @var  DataCatalogService */
    private $dataCatalogService;

    public function __construct(
        MotTestService $motTestService,
        DocumentService $documentService,
        DataCatalogService $dataCatalogService
    ) {
        $this->motTestService  = $motTestService;
        $this->documentService = $documentService;
        $this->dataCatalogService = $dataCatalogService;
    }

    /**
     * @param string $motTestNumber
     * @param int $userId
     *
     * @return MotTestDto
     *
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function createFromMotTestNumber($motTestNumber, $userId)
    {
        return $this->create(
            $motTestNumber,
            $this->motTestService->getMotTestData($motTestNumber),
            $userId
        );
    }

    /**
     * @param string      $motTestNumber
     * @param MotTestDto  $motTestData
     * @param int         $userId
     *
     * @return MotTestDto
     */
    public function create($motTestNumber, MotTestDto $motTestData, $userId)
    {
        if ($motTestData->getTestType() instanceof MotTestTypeDto
            && MotTestType::isVeAdvisory($motTestData->getTestType()->getCode())) {
            return $this->createVeAdvisoryCertificate($motTestNumber, $motTestData, $userId);
        }

        $testStatus = $motTestData->getStatus();

        if (in_array(
            $testStatus,
            [
                MotTestStatusName::FAILED,
                MotTestStatusName::ABANDONED,
                MotTestStatusName::ABORTED
            ]
        )) {
            if($this->isPrsTest($motTestData)){
                $this->createPrsPassCertificate($motTestData, $userId);
            }

            return $this->createFailCertificate($motTestNumber, $motTestData, $userId);
        }

        if ($testStatus === MotTestStatusName::PASSED) {
            if($this->isPrsTest($motTestData)){
                $this->createPrsFailCertificate($motTestData, $userId);
            }

            return $this->createPassCertificate($motTestNumber, $motTestData, $userId);
        }


        return $motTestData;
    }

    /**
     * @param MotTestDto $motTestData
     * @param int        $userId
     * @throws InvalidArgumentException
     * @return int
     */
    private function createPrsPassCertificate(MotTestDto $motTestData, $userId)
    {
        $motTestNumber = $motTestData->getPrsMotTestNumber();
        if ($motTestNumber === null) {
            throw new InvalidArgumentException();
        }

        $this->createPassCertificate(
            $motTestNumber,
            $this->motTestService->getMotTestData($motTestNumber),
            $userId
        );

        return $motTestNumber;
    }

    /**
     * @param MotTestDto $motTestData
     * @param int        $userId
     * @throws InvalidArgumentException
     * @return int
     */
    private function createPrsFailCertificate(MotTestDto $motTestData, $userId)
    {
        $motTestNumber = $motTestData->getPrsMotTestNumber();
        if ($motTestNumber === null) {
            throw new InvalidArgumentException();
        }

        $this->createFailCertificate(
            $motTestNumber,
            $this->motTestService->getMotTestData($motTestNumber),
            $userId
        );

        return $motTestNumber;
    }

    /**
     * @NOTE: at the moment there is no need to expose these individual
     * methods publicly; it's sufficient to just expose the one wrapper
     * method and let it take work out the most appropriate cert to generate.
     *
     * However, it's quite plausible that you may need to generate a document
     * explicitly; in which case do feel free to make these methods public.
     *
     * @param string     $id
     * @param MotTestDto $data
     * @param int        $userId
     *
     * @return MotTestDto
     */
    private function createPassCertificate($id, MotTestDto $data, $userId)
    {
        $certificateMapper = new Mapper\MotTestCertificateMapper($this->dataCatalogService);
        $documentName = 'MOT-Pass-Certificate';

        return $this->createCertificate(
            $id,
            $data,
            $certificateMapper,
            $documentName,
            $userId
        );
    }

    /**
     * @param string     $id
     * @param MotTestDto $data
     * @param string     $userId
     *
     * @return MotTestDto
     */
    public function createFailCertificate($id, MotTestDto $data, $userId)
    {
        $certificateMapper = new Mapper\MotTestFailureMapper($this->dataCatalogService);
        $documentName = 'MOT-Fail-Certificate';

        return $this->createCertificate(
            $id,
            $data,
            $certificateMapper,
            $documentName,
            $userId
        );
    }

    /**
     * @param string     $id
     * @param MotTestDto $data
     * @param int        $userId
     *
     * @return MotTestDto
     */
    private function createVeAdvisoryCertificate($id, MotTestDto $data, $userId)
    {
        $certificateMapper = new Mapper\MotTestAdvisoryNoticeMapper($this->dataCatalogService);
        $documentName = 'MOT-Advisory-Notice';

        return $this->createCertificate(
            $id,
            $data,
            $certificateMapper,
            $documentName,
            $userId
        );
    }

    /**
     * @param $motTestNumber
     * @param MotTestDto $data
     * @param AbstractMotTestMapper $certificateMapper
     * @param $documentName
     * @param $userId
     * @return MotTestDto
     */
    private function createCertificate(
        $motTestNumber,
        MotTestDto $data,
        AbstractMotTestMapper $certificateMapper,
        $documentName,
        $userId
    ) {
        if ($this->isRequiresDualLanguage($data)) {
            $certificateMapper->setDualLanguage(true);
            $documentName .= '-Dual';
        }

        if ($this->isNormalTest($data)) {
            $certificateMapper->setNormalTest(true);
        }

        $certificateMapper->addDataSource('MotTestData', (new ClassMethods(false))->extract($data));
        if ($motTestNumber) {
            $certificateMapper->addDataSource(
                'Additional',
                $this->motTestService->getAdditionalSnapshotData($motTestNumber)
            );
        }

        $snapShotData = $certificateMapper->mapData();

        $data->setDocument($this->documentService->createSnapshot($documentName, $snapShotData, $userId));

        if ($motTestNumber) {
            $this->motTestService->updateDocument($motTestNumber, $data->getDocument());
        }

        return $data;
    }

    public static function isRequiresDualLanguage(MotTestDto $data)
    {
        $site = $data->getVehicleTestingStation();

        return (ArrayUtils::tryGet($site, 'dualLanguage', false) === true);
    }

    private function isNormalTest(MotTestDto $data)
    {
        return ($data->getTestType() !== null)
        && ($data->getTestType()->getCode() === MotTestTypeCode::NORMAL_TEST);
    }

    private function isPrsTest(MotTestDto $motTestData)
    {
        return !is_null($motTestData->getPrsMotTestNumber());
    }

}
