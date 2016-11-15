<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Http\Response as HttpResponse;
use Dvsa\Mot\Behat\Support\Response;

abstract class AbstractData
{
    protected $userData;

    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    abstract function getLastResponse();

    /**
     * @param MotTestDto $mot
     * @return AuthenticatedUser
     */
    protected function getTesterFormMotTest(MotTestDto $mot)
    {
        return $this
            ->userData
            ->get($mot->getTester()->getUsername());
    }

    protected function hydrateResponse(Response $response)
    {
        return DtoHydrator::jsonToDto($response->getBody()->getData());
    }
}