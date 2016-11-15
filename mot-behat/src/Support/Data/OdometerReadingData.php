<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Params\OdometerReadingParams;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\OdometerReadingDTO;
use Zend\Http\Response as HttpResponse;

class OdometerReadingData
{
    private $odometerReading;
    private $userData;

    public function __construct(
        OdometerReading $odometerReading,
        UserData $userData
    )
    {
        $this->odometerReading = $odometerReading;
        $this->userData = $userData;
    }

    public function addMeterReadingByUser(MotTestDto $motTest, AuthenticatedUser $user, $value, $unit)
    {
        $this->odometerReading->addMeterReading($user->getAccessToken(), $motTest->getMotTestNumber(), $value, $unit);

        $odometerReading = new OdometerReadingDTO();
        $odometerReading->setValue($value);
        $odometerReading->setUnit($unit);

        $motTest->setOdometerReading($odometerReading);

        return $odometerReading;
    }

    public function addMeterReading(MotTestDto $motTest, $value, $unit)
    {
        $tester = $this->getTester($motTest);
        return $this->addMeterReadingByUser($motTest, $tester, $value, $unit);
    }

    public function addDefaultMeterReadingByUser(MotTestDto $motTest, AuthenticatedUser $user)
    {
        return $this->addMeterReadingByUser($motTest, $user, OdometerReadingData::generateUniqueOdometer(), OdometerReadingParams::MI);
    }

    public function addDefaultMeterReading(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);
        return $this->addDefaultMeterReadingByUser($motTest, $tester);
    }

    public function addNoMeterReadingToTestByUser(MotTestDto $motTest, AuthenticatedUser $user)
    {
        $this->odometerReading->addNoMeterReadingToTest($user->getAccessToken(), $motTest->getMotTestNumber());
    }

    public function addNoMeterReadingToTest(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);
        $this->addNoMeterReadingToTestByUser($motTest, $tester);
    }

    public function addOdometerNotReadToTestByUser(MotTestDto $motTest, AuthenticatedUser $user)
    {
        $this->odometerReading->addOdometerNotReadToTest($user->getAccessToken(), $motTest->getMotTestNumber());
    }

    public function addOdometerNotReadToTest(MotTestDto $motTest)
    {
        $tester = $this->getTester($motTest);
        $this->addOdometerNotReadToTestByUser($motTest, $tester);
    }

    public static function generateUniqueOdometer()
    {
        sleep(1);
        return date('Gis');
    }

    public function getLastResponse()
    {
        return $this->odometerReading->getLastResponse();
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
