<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;

class OrderSecurityCardStepService
{
    const NEW_STEP = 'new';
    const ADDRESS_STEP = 'address';
    const REVIEW_STEP = 'review';

    private $orderNewSecurityCardSessionService;

    public function __construct(OrderNewSecurityCardSessionService $sessionService)
    {
        $this->orderNewSecurityCardSessionService = $sessionService;
    }

    public function updateStepStatus($guid, $step, $status)
    {
        $sessionStore = $this->orderNewSecurityCardSessionService->loadByGuid($guid);
        $steps = (!empty($sessionStore[OrderNewSecurityCardSessionService::STEP_SESSION_STORE]))
            ? $sessionStore[OrderNewSecurityCardSessionService::STEP_SESSION_STORE] : [];

        if (empty($steps)) {
            throw new \Exception('Steps are not stored in session');
        }

        if (!isset($steps[$step])) {
            throw new \Exception('Step: ' .$step. ' is not a valid step');
        }

        if (!is_bool($status)) {
            throw new \Exception('Step status must be a boolean');
        }

        $steps[$step] = $status;

        $sessionStore[OrderNewSecurityCardSessionService::STEP_SESSION_STORE] = $steps;

        $this->orderNewSecurityCardSessionService->saveToGuid($guid, $sessionStore);

    }

    /**
     * @param $guid
     * @param $step
     * @return bool
     */
    public function isAllowedOnStep($guid, $step)
    {
        $sessionStore = $this->orderNewSecurityCardSessionService->loadByGuid($guid);
        $steps = $sessionStore[OrderNewSecurityCardSessionService::STEP_SESSION_STORE];

        // If steps are not loaded return false
        if (is_null($steps) || !is_array($steps)) {
            return false;
        }

        if (!isset($steps[$step])) {
            return false;
        }

        $previousValue = null;

        foreach ($steps as $key => $value) {
            if ($step == $key) {
                return $previousValue;
            }
            $previousValue = $value;
        }
        return false;
    }

    /**
     * Returns a list of steps in the journey
     * @return array
     */
    public function getSteps()
    {
        return [
            self::NEW_STEP,
            self::ADDRESS_STEP,
            self::REVIEW_STEP,
        ];
    }
}