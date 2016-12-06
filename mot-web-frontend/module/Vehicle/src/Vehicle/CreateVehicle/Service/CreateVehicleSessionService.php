<?php

namespace Vehicle\CreateVehicle\Service;

use Core\Service\SessionService;

class CreateVehicleSessionService extends SessionService
{
    const UNIQUE_KEY = 'create_vehicle_store';
    const STEP_KEY = 'steps';
    const API_DATA = 'api_data';
    const USER_DATA = 'user_data';
}