<?php
namespace PersonApi\Controller;

use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\MotTestingAnnualCertificateService;

class MotTestingAnnualCertificateController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $motTestingAnnualCertificateService;
    private $deserializer;

    public function __construct(
        MotTestingAnnualCertificateService $motTestingAnnualCertificateService,
        DtoReflectiveDeserializer $deserializer
    ) {
        $this->setIdentifierName("certificateId");
        $this->motTestingAnnualCertificateService = $motTestingAnnualCertificateService;
        $this->deserializer = $deserializer;
    }

    public function getList()
    {
        $personId = (int)$this->params()->fromRoute("id");
        $group = $this->params()->fromRoute('group');

        return $this->returnDto($this->motTestingAnnualCertificateService->getListByGroup($personId, $group));
    }

    public function get($certificateId)
    {
        $personId = (int)$this->params()->fromRoute("id");
        $group = $this->params()->fromRoute('group');

        return $this->returnDto($this->motTestingAnnualCertificateService->get($certificateId, $personId, $group));
    }

    public function create($data)
    {
        $personId = (int)$this->params()->fromRoute('id');
        $group = $this->params()->fromRoute('group');

        /** @var MotTestingAnnualCertificateDto $dto */
        $dto = $this->deserializer->deserialize($data, MotTestingAnnualCertificateDto::class);

        return $this->returnDto($this->motTestingAnnualCertificateService->create($personId, $group, $dto));
    }

    public function update($certificateId, $data)
    {
        $personId = (int)$this->params()->fromRoute('id');
        $group = $this->params()->fromRoute('group');

        /** @var MotTestingAnnualCertificateDto $dto */
        $dto = $this->deserializer->deserialize($data, MotTestingAnnualCertificateDto::class);

        return $this->returnDto($this->motTestingAnnualCertificateService->update($certificateId, $personId, $group, $dto));
    }

    public function delete($certificateId)
    {
        $personId = (int)$this->params()->fromRoute('id');
        $group = $this->params()->fromRoute('group');

        $this->motTestingAnnualCertificateService->delete($personId, $group, $certificateId);

        return ApiResponse::jsonOk();
    }
}