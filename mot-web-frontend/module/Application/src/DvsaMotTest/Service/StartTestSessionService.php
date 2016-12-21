<?php

namespace DvsaMotTest\Service;

use Core\Service\SessionService;

class StartTestSessionService extends SessionService
{
    const UNIQUE_KEY = 'start_test_change_vehicle_store';
    const ALLOWED_CHANGES = 'allowed_changes';
    const USER_DATA = 'user_data';
}