<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;

class ReasonForRejectionData extends AbstractData
{
    private $reasonForRejection;
    private $rfrClient;

    public function __construct(ReasonForRejection $reasonForRejection, UserData $userData, SearchReasonForRejectionInterface $rfrClient)
    {
        parent::__construct($userData);

        $this->reasonForRejection = $reasonForRejection;
        $this->rfrClient = $rfrClient;
    }

    public function searchWithParams($vehicleClassCode, $term, $audience = SearchReasonForRejectionInterface::TESTER_ROLE_FLAG, $page = 1)
    {
        return $this->rfrClient->search(
            $term,
            $vehicleClassCode,
            $audience,
            $page
        );
    }

    public function search(MotTestDto $mot, $term, $audience, $page)
    {
        return $this->searchWithParams($mot->getVehicleClass()->getCode(), $term, $audience, $page);
    }

    public function searchWithDefaultParams(MotTestDto $mot)
    {
        return $this->search($mot, "brake", "t", 1);
    }

    public function listTestItemSelectorsByUser(AuthenticatedUser $user, MotTestDto $mot, $rootItemId = 0)
    {
        $this->reasonForRejection->listTestItemSelectors(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $rootItemId
        );
    }

    public function listTestItemSelectors(MotTestDto $mot, $rootItemId = 0)
    {
        $tester = $this->getTesterFormMotTest($mot);
        $this->listTestItemSelectorsByUser($tester, $mot, $rootItemId);
    }

    public function addPrsByUser(AuthenticatedUser $user, MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejection->addPrs(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $rfrId
        );
    }

    public function addPrs(MotTestDto $mot, $rfrId)
    {
        $this->addPrsByUser($this->getTesterFormMotTest($mot), $mot, $rfrId);
    }

    public function addDefaultPrsByUser(AuthenticatedUser $user, MotTestDto $mot)
    {
        $rfrId = ($mot->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3)
            ? ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
            : ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;

        $this->addPrsByUser($user, $mot, $rfrId);

    }

    public function addDefaultPrs(MotTestDto $mot)
    {
        $this->addDefaultPrsByUser($this->getTesterFormMotTest($mot), $mot);

    }

    public function addFailureByUser(AuthenticatedUser $user, MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejection->addFailure($user->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
    }

    public function addFailure(MotTestDto $mot, $rfrId)
    {
        $this->addFailureByUser($this->getTesterFormMotTest($mot), $mot, $rfrId);
    }

    public function addDefaultFailureByUser(AuthenticatedUser $user, MotTestDto $mot)
    {
        $rfrId = ($mot->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3)
            ? ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
            : ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;

        $this->addFailureByUser($user, $mot, $rfrId);
    }

    public function addDefaultFailure(MotTestDto $mot)
    {
        $this->addDefaultFailureByUser($this->getTesterFormMotTest($mot), $mot);
    }

    public function editRFRByUser(AuthenticatedUser $user, MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejection->editRFR($user->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
    }

    public function editRFR(MotTestDto $mot, $rfrId)
    {
        $this->editRFRByUser($this->getTesterFormMotTest($mot), $mot, $rfrId);
    }

    public function addAdvisoryByUser(AuthenticatedUser $user, MotTestDto $mot, $rfrId)
    {
        $this->reasonForRejection->addAdvisory($user->getAccessToken(), $mot->getMotTestNumber(), $rfrId);
    }

    public function addAdvisory(MotTestDto $mot, $rfrId)
    {
        $this->addAdvisoryByUser($this->getTesterFormMotTest($mot), $mot, $rfrId);
    }

    public function getLastResponse()
    {
        return $this->reasonForRejection->getLastResponse();
    }
}
