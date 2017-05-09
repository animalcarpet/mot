<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Validation\ContingencyTestValidator;

/**
 * Contingency Controller.
 */
class ContingencyTestController extends AbstractDvsaRestfulController
{
    const ERR_NOT_A_DTO = 'Invalid / missing DTO in request';
    const ERR_DTO_INCOMPLETE = 'DTO is not fully populated';

    const SESSION_NAME = 'EmergencyLogInfo';

    /**
     * @var ContingencyTestValidator
     */
    private $validator;

    /**
     * ContingencyTestController constructor.
     *
     * @param ContingencyTestValidator $contingencyTestValidator
     */
    public function __construct(ContingencyTestValidator $contingencyTestValidator)
    {
        $this->validator = $contingencyTestValidator;
    }

    /**
     * Create a new emergency test. This involves hydrating the DTO which MUST be an instance of ContingencyTestDto.
     *
     * @param $data array presumably from a POST request
     *
     * @throws BadRequestException unless a ContingencyTestDto is hydrated
     *
     * @return mixed
     */
    public function create($data)
    {
        // Validate our POST data
        $validationResult = $this->validator->validate($data);
        if (false === $validationResult->isValid()) {
            return $this->createValidationProblemResponseModel($validationResult->getFlattenedMessages());
        }

        /** @var ContingencyTestDto $dto */
        $dto = DtoHydrator::jsonToDto($data);
        if (!$dto instanceof ContingencyTestDto) {
            throw new BadRequestException(self::ERR_NOT_A_DTO, BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        $responseData = ['emergencyLogId' => $this->validator->getEmergencyLog()->getId()];

        return ApiResponse::jsonOk($responseData);
    }
}
