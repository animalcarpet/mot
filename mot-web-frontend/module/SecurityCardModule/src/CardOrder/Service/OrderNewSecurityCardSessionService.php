<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service;

use Core\Service\SessionService;

class OrderNewSecurityCardSessionService extends SessionService
{
    const UNIQUE_KEY = 'order_new_security_card';
    const ADDRESS_SESSION_STORE = 'USER_ADDRESS';
    const STEP_SESSION_STORE = 'steps';
    const ADDRESS_STEP_STORE = 'addressStep';
    const HAS_ORDERED_STORE = 'hasOrdered';

    public function loadByGuid($guid)
    {
        return $this->load(self::UNIQUE_KEY.$guid);
    }

    public function saveToGuid($guid, $value)
    {
        $this->save(self::UNIQUE_KEY.$guid, $value);
    }

    public function clearByGuid($guid)
    {
        $this->sessionContainer->offsetUnset(self::UNIQUE_KEY.$guid);
    }
}
