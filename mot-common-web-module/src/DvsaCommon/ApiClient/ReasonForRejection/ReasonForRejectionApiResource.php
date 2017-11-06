<?php

namespace DvsaCommon\ApiClient\ReasonForRejection;

use DvsaCommon\HttpRestJson\AbstractApiResource;
use DvsaCommon\ReasonForRejection\ReasonForRejectionResponseDto;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;

class ReasonForRejectionApiResource extends AbstractApiResource implements SearchReasonForRejectionInterface
{
    public function search(string $searchTerm, string $vehicleClassCode, string $audience, int $page): SearchReasonForRejectionResponseInterface
    {
        $query = http_build_query([
            "searchTerm" => $searchTerm,
            "vehicleClass" => $vehicleClassCode,
            "audience" => $audience,
            "page" => $page,
        ]);

        return $this->getSingle(ReasonForRejectionResponseDto::class, sprintf('reasons-for-rejection/search?%s', $query));
    }
}