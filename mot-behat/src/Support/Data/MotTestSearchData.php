<?php
namespace Dvsa\Mot\Behat\Support\Data;

use DvsaCommon\Collection\Collection;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Http\Response as HttpResponse;

class MotTestSearchData
{
    private $motTest;

    public function __construct(MotTest $motTest)
    {
        $this->motTest = $motTest;
    }

    public function searchBySiteNumber(AuthenticatedUser $user, $siteNumber)
    {
        return $this->search($user, ['siteNr' => $siteNumber]);
    }

    public function searchByTestNumber(AuthenticatedUser $user, $testNumber)
    {
        return $this->search($user, ['testNumber' => $testNumber]);
    }

    public function searchByVehicleRegNr(AuthenticatedUser $user, $vehicleRegNr)
    {
        return $this->search($user, ['vehicleRegNr' => $vehicleRegNr]);
    }

    public function search(AuthenticatedUser $user, array $params = [])
    {
        $response = $this->motTest->searchMOTTest(
            $user->getAccessToken(),
            $params
        );

        if ($response->getStatusCode() !== HttpResponse::STATUS_CODE_200) {
            throw new \Exception("Mot tests not found");
        }

        $motTests = $response->getBody()->getData()["data"];
        $searchCollection = new Collection(MotTestDto::class);
        foreach ($motTests as $test) {
            $test["_class"] = MotTestDto::class;
            $dto = $this->hydrateToDto($test);
            $searchCollection->add($dto, $dto->getMotTestNumber());
        }

        return $searchCollection;
    }

    /**
     * @param array $mot
     * @return MotTestDto
     */
    private function hydrateToDto(array $mot)
    {
        return DtoHydrator::jsonToDto($mot);
    }
}
