<?php
namespace DvsaCommon\ReasonForRejection;

interface SearchReasonForRejectionInterface
{
    const ITEMS_PER_PAGE = 10;
    const TESTER_ROLE_FLAG = "t";
    const VEHICLE_EXAMINER_ROLE_FLAG = "v";

    public function search(string $searchTerm, string $vehicleClassCode, string $audience, int $page): SearchReasonForRejectionResponseInterface;
}
