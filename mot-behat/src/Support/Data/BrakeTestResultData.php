<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\RollerBrakeTestClass3To7;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\VehicleClassCode;

class BrakeTestResultData extends AbstractData
{
    private $brakeTestResult;

    public function __construct(
        BrakeTestResult $brakeTestResult,
        UserData $userData
    )
    {
        parent::__construct($userData);

        $this->brakeTestResult = $brakeTestResult;
    }

    public function addDefaultBrakeTestDecelerometerByUser(MotTestDto $mot, AuthenticatedUser $user)
    {
        if ($mot->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3) {
            $this->addDefaultBrakeTestDecelerometerClass1To2ByUser($mot, $user);
        } else {
            $this->addBrakeTestDecelerometerClass3To7ByUser($mot, $user);
        }
    }

    public function addDefaultBrakeTestDecelerometer(MotTestDto $mot)
    {
        $this->addDefaultBrakeTestDecelerometerByUser($mot, $this->getTester($mot));
    }

    public function addBrakeTestDecelerometerClass1To2ByUser(MotTestDto $mot, AuthenticatedUser $user, $control1BrakeEfficiency, $control2BrakeEfficiency)
    {
        $this->brakeTestResult->addBrakeTestDecelerometerClass1To2(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $control1BrakeEfficiency,
            $control2BrakeEfficiency
        );
    }

    public function addBrakeTestDecelerometerClass1To2(MotTestDto $mot, $control1BrakeEfficiency, $control2BrakeEfficiency)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestDecelerometerClass1To2ByUser($mot, $tester, $control1BrakeEfficiency, $control2BrakeEfficiency);
    }

    public function addDefaultBrakeTestDecelerometerClass1To2ByUser(MotTestDto $mot, AuthenticatedUser $user)
    {
        $this->addBrakeTestDecelerometerClass1To2ByUser($mot, $user, 66, 65);
    }

    public function addDefaultBrakeTestDecelerometerClass1To2(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $this->addDefaultBrakeTestDecelerometerClass1To2ByUser($mot, $tester);
    }

    public function addBrakeTestDecelerometerClass1To2WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, array $data)
    {
        $this->brakeTestResult->addBrakeTestDecelerometerClass1To2WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestDecelerometerClass1To2WithCustomData(MotTestDto $mot, array $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestDecelerometerClass1To2WithCustomDataByUser($mot, $tester, $data);
    }

    public function addBrakeTestDecelerometerClass3To7ByUser(MotTestDto $mot, AuthenticatedUser $user)
    {
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($user->getAccessToken(), $mot->getMotTestNumber());
    }

    public function addBrakeTestDecelerometerClass3To7(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestDecelerometerClass3To7ByUser($mot, $tester);
    }

    public function addBrakeTestDecelerometerClass3To7WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, array $data)
    {
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestDecelerometerClass3To7WithCustomData(MotTestDto $mot, array $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestDecelerometerClass3To7WithCustomDataByUser($mot, $tester, $data);
    }

    public function addBrakeTestForRollerClass1To2WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, array $data)
    {
        $this->brakeTestResult->addBrakeTestForRollerClass1To2WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestForRollerClass1To2WithCustomData(MotTestDto $mot, array $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestForRollerClass1To2WithCustomDataByUser($mot, $tester, $data);
    }

    public function addBrakeTestRollerClass3To7ByUser(MotTestDto $mot, AuthenticatedUser $user)
    {
        $this->brakeTestResult->addBrakeTestRollerClass3To7($user->getAccessToken(), $mot->getMotTestNumber());
    }

    public function addBrakeTestRollerClass3To7(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestRollerClass3To7ByUser($mot, $tester);
    }

    public function addBrakeTestForPlateClass1To2WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, array $data)
    {
        $this->brakeTestResult->addBrakeTestForPlateClass1To2WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestForPlateClass1To2WithCustomData(MotTestDto $mot, array $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestForPlateClass1To2WithCustomDataByUser($mot, $tester, $data);
    }

    public function addBrakeTestGradientClass1To2WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, array $data)
    {
        $this->brakeTestResult->addBrakeTestGradientClass1To2WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestGradientClass1To2WithCustomData(MotTestDto $mot, array $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestGradientClass1To2WithCustomDataByUser($mot, $tester, $data);
    }

    public function addBrakeTestFloorClass1To2WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, array $data)
    {
        $this->brakeTestResult->addBrakeTestFloorClass1To2WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestFloorClass1To2WithCustomData(MotTestDto $mot, array $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestFloorClass1To2WithCustomDataByUser($mot, $tester, $data);
    }

    public function addBrakeTestPlateClass3to7ByUser(MotTestDto $mot, AuthenticatedUser $user)
    {
        $this->brakeTestResult->addBrakeTestPlateClass3to7($user->getAccessToken(), $mot->getMotTestNumber());
    }

    public function addBrakeTestPlateClass3to7(MotTestDto $mot)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestPlateClass3to7ByUser($mot, $tester);
    }

    public function addBrakeTestRollerClass3To7WithCustomDataByUser(MotTestDto $mot, AuthenticatedUser $user, RollerBrakeTestClass3To7 $data)
    {
        $this->brakeTestResult->addBrakeTestRollerClass3To7WithCustomData(
            $user->getAccessToken(),
            $mot->getMotTestNumber(),
            $data
        );
    }

    public function addBrakeTestRollerClass3To7WithCustomData(MotTestDto $mot, RollerBrakeTestClass3To7 $data)
    {
        $tester = $this->getTester($mot);
        $this->addBrakeTestRollerClass3To7WithCustomDataByUser($mot, $tester, $data);
    }

    public function getLastResponse()
    {
        return $this->brakeTestResult->getLastResponse();
    }

    /**
     * @param MotTestDto $mot
     * @return AuthenticatedUser
     */
    protected function getTester(MotTestDto $mot)
    {
        return $this
            ->userData
            ->get($mot->getTester()->getUsername());
    }
}
