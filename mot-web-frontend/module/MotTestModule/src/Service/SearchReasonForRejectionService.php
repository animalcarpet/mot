<?php
namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use Application\Paginator\PaginatorInterface;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;

class SearchReasonForRejectionService
{
    private $motTestService;
    private $motAuthorisationService;
    private $reasonForRejectionClient;

    public function __construct(
        MotTestService $motTestService,
        MotAuthorisationServiceInterface $motAuthorisationService,
        SearchReasonForRejectionInterface $reasonForRejectionClient
    )
    {
        $this->motTestService = $motTestService;
        $this->motAuthorisationService = $motAuthorisationService;
        $this->reasonForRejectionClient = $reasonForRejectionClient;
    }

    public function search(string $searchTerm, string $vehicleClassCode, int $page): PaginatorInterface
    {
        $this->motAuthorisationService->assertGranted(PermissionInSystem::RFR_LIST);

        $response = $this
            ->reasonForRejectionClient
            ->search($searchTerm, $vehicleClassCode, $this->getAudience(), $page);

        return new ReasonForRejectionPaginator($response);
    }

    private function getAudience(): string
    {
        $audience = SearchReasonForRejectionInterface::TESTER_ROLE_FLAG;
        if ($this->motAuthorisationService->isGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED)) {
            $audience = SearchReasonForRejectionInterface::VEHICLE_EXAMINER_ROLE_FLAG;
        }

        return $audience;
    }
}
