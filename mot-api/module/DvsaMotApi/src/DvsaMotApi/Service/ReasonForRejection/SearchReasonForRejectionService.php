<?php

namespace DvsaMotApi\Service\ReasonForRejection;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\ReasonForRejection\ReasonForRejectionDtoMapper;
use DvsaCommon\ReasonForRejection\ReasonForRejectionResponseDto;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use DvsaEntities\Repository\RfrRepository;

class SearchReasonForRejectionService implements SearchReasonForRejectionInterface
{
    private $authorisationService;
    private $rfrRepository;

    public function __construct(AuthorisationServiceInterface $authorisationService, RfrRepository $rfrRepository)
    {
        $this->authorisationService = $authorisationService;
        $this->rfrRepository = $rfrRepository;
    }

    public function search(string $searchTerm, string $vehicleClassCode, string $audience, int $page): SearchReasonForRejectionResponseInterface
    {
        $this->authorisationService->assertGranted(PermissionInSystem::RFR_LIST);
        if ($audience === SearchReasonForRejectionInterface::VEHICLE_EXAMINER_ROLE_FLAG) {
            $this->authorisationService->assertGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED);
        }

        if (empty($searchTerm)) {
            return $this->createResponse([], 0, 1);
        }

        $totalCount = $this->rfrRepository->count($searchTerm, $vehicleClassCode, $audience);
        $lastPage = (int) ceil($totalCount/SearchReasonForRejectionInterface::ITEMS_PER_PAGE);

        if ($lastPage < 1) {
            $page = 1;
        } elseif ($lastPage < $page) {
            $page  = $lastPage;
        }

        $offset = SearchReasonForRejectionInterface::ITEMS_PER_PAGE * ($page - 1);

        $reasonsForRejection = $this->rfrRepository->findBySearchQuery($searchTerm, $vehicleClassCode, $audience, $offset, SearchReasonForRejectionInterface::ITEMS_PER_PAGE);
        $reasonForRejectionTypeConverterService = new ReasonForRejectionTypeConverterService();
        $processedRfrs = [];
        foreach ($reasonsForRejection as $rfr) {
            $processedRfr = $reasonForRejectionTypeConverterService->convert($rfr);
            $processedRfr["vehicleClassCode"] = $vehicleClassCode;

            $processedRfrs[] = $processedRfr;
        }

        $dtoArray = ReasonForRejectionDtoMapper::mapMany($processedRfrs);

        return $this->createResponse($dtoArray, $totalCount, $page);
    }

    private function createResponse(array $reasonsForRejection, int $totalCount, int $page): SearchReasonForRejectionResponseInterface
    {
        $reasonForRejectionResponseDto = new ReasonForRejectionResponseDto();
        $reasonForRejectionResponseDto->setData($reasonsForRejection);
        $reasonForRejectionResponseDto->setTotalCount($totalCount);
        $reasonForRejectionResponseDto->setItemPerPage(SearchReasonForRejectionInterface::ITEMS_PER_PAGE);
        $reasonForRejectionResponseDto->setPage($page);

        return $reasonForRejectionResponseDto;
    }
}
