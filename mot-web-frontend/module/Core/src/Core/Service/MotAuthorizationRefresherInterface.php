<?php

namespace Core\Service;

/**
 * Exposes authorization refresh functionality.
 */
interface MotAuthorizationRefresherInterface
{
    public function refreshAuthorization();
}
