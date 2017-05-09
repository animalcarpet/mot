<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service;

use Core\Service\SessionService;

class LostOrForgottenSessionService extends SessionService
{
    const UNIQUE_KEY = 'security_card_lost_and_forgotten';
}
