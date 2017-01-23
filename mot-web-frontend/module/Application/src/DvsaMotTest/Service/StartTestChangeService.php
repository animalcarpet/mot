<?php

namespace DvsaMotTest\Service;

use Core\Routing\MotTestRoutes;
use Core\Routing\VehicleRoutes;
use Zend\View\Helper\Url;

class StartTestChangeService
{
    const CHANGE_CLASS = 'class';
    const CHANGE_COUNTRY = 'country';
    const CHANGE_COLOUR = 'colour';
    const CHANGE_ENGINE = 'engine';
    const CHANGE_MAKE = 'make';
    const CHANGE_MODEL = 'model';
    const NO_REGISTRATION = 'noRegistration';
    const URL = 'url';
    const SOURCE = 'source';
    const PRIMARY_COLOUR = 'primaryColour';
    const SECONDARY_COLOUR = 'secondaryColour';
    const FUEL_TYPE = 'fuelType';
    const CYLINDER_CAPACITY = 'cylinderCapacity';

    const VALUE_CHANGED = true;
    const VALUE_NOT_CHANGED = false;

    /** @var  StartTestSessionService */
    private $startTestSessionService;

    /** @var  Url */
    private $url;

    /**
     * StartTestChangeService constructor.
     *
     * @param StartTestSessionService $startTestSessionService
     * @param Url                     $url
     */
    public function __construct(
        StartTestSessionService $startTestSessionService,
        Url $url
    )
    {
        $this->startTestSessionService = $startTestSessionService;
        $this->url = $url;
    }

    /**
     * @param $value
     * @param $status
     *
     * @throws \Exception
     */
    public function updateChangedValueStatus($value, $status)
    {
        $sessionStore = $this->startTestSessionService->load(StartTestSessionService::UNIQUE_KEY);
        $changes = (!empty($sessionStore[StartTestSessionService::VEHICLE_CHANGE_STATUS]))
            ? $sessionStore[StartTestSessionService::VEHICLE_CHANGE_STATUS] : [];

        if (empty($changes)) {
            throw new \Exception('Changes are not stored in session');
        }

        if (!isset($changes[$value])) {
            throw new \Exception('Change value: ' .$value. ' is not an allowed change');
        }

        if (!is_bool($status)) {
            throw new \Exception('Changed value status must be a boolean');
        }

        $changes[$value] = $status;

        $sessionStore[StartTestSessionService::VEHICLE_CHANGE_STATUS] = $changes;

        $this->startTestSessionService->save(StartTestSessionService::UNIQUE_KEY, $sessionStore);
    }

    /**
     * @param $value
     *
     * @return mixed
     * @throws \Exception
     */
    public function getChangedValue($value)
    {
        if (!in_array($value, $this->getVehicleChanges())) {
            throw new \Exception("Change value $value is not a valid change.");
        }

        $sessionStore = $this->startTestSessionService->load(StartTestSessionService::UNIQUE_KEY);
        $changedValues = $sessionStore[StartTestSessionService::USER_DATA];

        return $changedValues[$value];
    }

    /**
     * @param       $change
     * @param array $changeData
     *
     * @throws \Exception
     */
    public function saveChange($change, array $changeData)
    {
        if (!in_array($change, $this->getVehicleChanges())) {
            throw new \Exception("Change value $change is not an allowed change.");
        }

        $sessionStore = $this->startTestSessionService->load(StartTestSessionService::UNIQUE_KEY);
        $changeStore = $sessionStore[StartTestSessionService::USER_DATA];

        $changeStore[$change] = $changeData;

        $sessionStore[StartTestSessionService::USER_DATA] = $changeStore;
        $this->startTestSessionService->save(StartTestSessionService::UNIQUE_KEY, $sessionStore);
    }

    public function loadAllowedChangesIntoSession()
    {
        $this->startTestSessionService->clear();
        $sessionStore = [];

        $allowedChanges = [];

        foreach ($this->getVehicleChanges() as $allowedChange) {
            $allowedChanges[$allowedChange] = self::VALUE_NOT_CHANGED;
        }

        $sessionStore[StartTestSessionService::VEHICLE_CHANGE_STATUS] = $allowedChanges;
        $this->startTestSessionService->save(StartTestSessionService::UNIQUE_KEY, $sessionStore);
    }

    /**
     * @param $changedValue
     *
     * @return bool|mixed
     */
    public function isValueChanged($changedValue)
    {
        $sessionStore = $this->startTestSessionService->load(StartTestSessionService::UNIQUE_KEY);
        $changes = (!empty($sessionStore[StartTestSessionService::VEHICLE_CHANGE_STATUS]))
            ? $sessionStore[StartTestSessionService::VEHICLE_CHANGE_STATUS] : null;

        // If allowed changes are not loaded return false
        if (is_null($changes) || !is_array($changes)) {
            return false;
        }

        if (!isset($changes[$changedValue])) {
            return false;
        }
        
        foreach ($changes as $key => $value) {
            if ($changedValue == $key) {
                return $value;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isMakeAndModelChanged()
    {
        if ($this->isValueChanged(self::CHANGE_MAKE) &&
            $this->isValueChanged(self::CHANGE_MODEL)) {
            return true;
        }

        return false;
    }

    /**
     * @param      $obfuscatedVehicleId
     * @param null $property
     *
     * @return mixed
     */
    public function underTestReturnUrl($obfuscatedVehicleId, $property = null)
    {
        return MotTestRoutes::of($this->url)->vehicleMotTest(
            $this->getChangedValue(StartTestChangeService::URL)['url'],
            $obfuscatedVehicleId,
            $this->getChangedValue(StartTestChangeService::NO_REGISTRATION)['noRegistration'],
            $this->getChangedValue(StartTestChangeService::SOURCE)['source'],
            $property
        );
    }

    /**
     * @param $obfuscatedVehicleId
     *
     * @return mixed
     */
    public function vehicleExaminerReturnUrl($obfuscatedVehicleId)
    {
        return VehicleRoutes::of($this->url)->vehicleDetails($obfuscatedVehicleId);
    }

    /**
     * @return array
     */
    private function getVehicleChanges()
    {
        return [
            self::CHANGE_CLASS,
            self::CHANGE_COLOUR,
            self::CHANGE_COUNTRY,
            self::CHANGE_ENGINE,
            self::CHANGE_MAKE,
            self::CHANGE_MODEL,
            self::NO_REGISTRATION,
            self::SOURCE,
            self::URL
        ];
    }
}