<?php

use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use Dvsa\Mot\AuditApi\Factory\HistoryAuditServiceFactory;

$s = [];
$s['factories'] = [
    HistoryAuditService::class => HistoryAuditServiceFactory::class,
];

return $s;
