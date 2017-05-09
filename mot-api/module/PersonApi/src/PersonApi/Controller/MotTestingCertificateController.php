<?php

namespace PersonApi\Controller;

use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\MotTestingCertificateService;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;

class MotTestingCertificateController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $motTestingCertificateService;
    private $deserializer;

    public function __construct(MotTestingCertificateService $motTestingCertificateService, DtoReflectiveDeserializer $deserializer)
    {
        $this->setIdentifierName('group');
        $this->motTestingCertificateService = $motTestingCertificateService;
        $this->deserializer = $deserializer;
    }

    public function get($group)
    {
        $personId = (int) $this->params()->fromRoute('id');

        return $this->returnDto($this->motTestingCertificateService->get($this->convertGroup($group), $personId));
    }

    public function getList()
    {
        $personId = (int) $this->params()->fromRoute('id');

        return $this->returnDto($this->motTestingCertificateService->getList($personId));
    }

    public function create($data)
    {
        $personId = (int) $this->params()->fromRoute('id');
        /** @var MotTestingCertificateDto $dto */
        $dto = $this->deserializer->deserialize($data, MotTestingCertificateDto::class);

        return $this->returnDto($this->motTestingCertificateService->create($personId, $dto));
    }

    public function update($group, $data)
    {
        $personId = (int) $this->params()->fromRoute('id');
        /** @var MotTestingCertificateDto $dto */
        $dto = $this->deserializer->deserialize($data, MotTestingCertificateDto::class);

        return $this->returnDto($this->motTestingCertificateService->update($this->convertGroup($group), $personId, $dto));
    }

    public function delete($group)
    {
        $personId = (int) $this->params()->fromRoute('id');
        $this->motTestingCertificateService->remove($personId, $this->convertGroup($group));

        return ApiResponse::jsonOk();
    }

    private function convertGroup($group)
    {
        return ArrayUtils::tryGet(
            [
                'a' => VehicleClassGroupCode::BIKES,
                'b' => VehicleClassGroupCode::CARS_ETC,
            ],
            $group
        );
    }
}
