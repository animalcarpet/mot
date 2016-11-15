<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Http\Response as HttpResponse;

class MotTestSearchData extends AbstractData
{
    private $motTest;

    public function __construct(MotTest $motTest, UserData $userData)
    {
        parent::__construct($userData);

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

        $motTests = $response->getBody()->getData()["data"];
        $searchCollection = new DataCollection(MotTestDto::class);
        foreach ($motTests as $test) {
            $test["_class"] = MotTestDto::class;
            /** @var MotTestDto $dto */
            $dto = $this->hydrateToDto($test);
            $searchCollection->add($dto, $dto->getMotTestNumber());
        }

        return $searchCollection;
    }

    /**
     * @param AuthenticatedUser $user
     * @param array $params
     * @return SearchResultDto
     */
    public function searchMotTestHistory(AuthenticatedUser $user, array $params)
    {
        $response = $this->motTest->searchMotTestHistory(
            $user->getAccessToken(),
            $params
        );

        return $this->hydrateToDto($response->getBody()->getData());
    }

    public function searchMotTestHistoryForTester(AuthenticatedUser $requestor, AuthenticatedUser $tester)
    {
        return $this->searchMotTestHistory($requestor, ["tester" => $tester->getUserId()]);
    }

    private function hydrateToDto(array $data)
    {
        return DtoHydrator::jsonToDto($data);
    }

    public function getLastResponse()
    {
        return $this->motTest->getLastResponse();
    }
}
