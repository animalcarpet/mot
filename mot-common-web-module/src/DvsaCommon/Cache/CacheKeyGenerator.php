<?php
declare(strict_types=1);

namespace DvsaCommon\Cache;


interface CacheKeyGenerator
{
    /**
     * @param array $args input arguments
     * @return string caching key
     */
    public function generateKey(...$args): string;
}