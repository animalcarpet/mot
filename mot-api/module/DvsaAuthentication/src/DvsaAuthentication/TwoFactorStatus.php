<?php

namespace DvsaAuthentication;

class TwoFactorStatus
{
    const ACTIVE = 'ACTIVE';
    const AWAITING_CARD_ACTIVATION = 'AWAITING_CARD_ACTIVATION';
    const AWAITING_CARD_ORDER = 'AWAITING_CARD_ORDER';
    const INACTIVE_TRADE_USER = 'INACTIVE_TRADE_USER';
}
