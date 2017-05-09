<?php

namespace DvsaEntities\Repository;

/**
 * Interface ConfigurationRepositoryInterface.
 */
interface ConfigurationRepositoryInterface
{
    /**
     * @param string $paramKey
     *
     * @return mixed
     */
    public function getValue($paramKey);
}
