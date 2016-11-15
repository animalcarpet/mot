<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\VehicleClassCode;

class ReasonForRejectionData extends AbstractData
{
    private $reasonForRejection;

    public function __construct(ReasonForRejection $reasonForRejection, UserData $userData)
    {
        parent::__construct($userData);

        $this->reasonForRejection = $reasonForRejection;
    }

    public function searchByUser(AuthenticatedUser $user, MotTestDto $mot, $term, $start, $end)
    {
        $this->reasonForRejection->search(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $term,
            $start,
            $end
        );
    }

    public function search(MotTestDto $mot, $term, $start, $end)
    {
        $tester = $this->getTesterFormMotTest($mot);
        $this->searchByUser($tester, $mot, $term, $start, $end);

    }

    public function searchWithDefaultParamsByUser(AuthenticatedUser $user, MotTestDto $mot)
    {
        $this->searchByUser($user, $mot, "brake", 0, 2);
    }

    public function searchWithDefaultParams(MotTestDto $mot)
    {
        $tester = $this->getTesterFormMotTest($mot);
        $this->searchWithDefaultParamsByUser($tester, $mot);
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
