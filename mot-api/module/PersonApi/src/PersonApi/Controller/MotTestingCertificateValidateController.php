<?php

namespace PersonApi\Controller;

use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\MotTestingCertificateService;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;

class MotTestingCertificateValidateController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $motTestingCertificateService;
    private $deserializer;

    public function __construct(MotTestingCertificateService $motTestingCertificateService, DtoReflectiveDeserializer $deserializer)
    {
        $this->motTestingCertificateService = $motTestingCertificateService;
        $this->deserializer = $deserializer;
    }

    public function create($data)
    {
        /** @var MotTestingCertificateDto $dto */
        $dto = $this->deserializer->deserialize($data, MotTestingCertificateDto::class);
        $this->motTestingCertificateService->validate($dto);

        return ApiResponse::jsonOk();
    }
}
