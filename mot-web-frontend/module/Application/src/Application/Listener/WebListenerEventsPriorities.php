<?php

namespace Application\Listener;

final class WebListenerEventsPriorities
{
    const DISPATCH_CARD_VALIDATION = 1;
    const DISPATCH_REGISTER_CARD_HARD_STOP = 2;
    const DISPATCH_CHANGE_TEMP_PASSWORD = 3;
    const DISPATCH_CLAIM_ACCOUNT = 4;
    const DISPATCH_RESET_EXPIRED_PASSWORD = 5;
    const DISPATCH_ERROR_HANDLE_ERROR = 0;
    const ROUTE_STOP_PROPAGATION = -10000;
}
