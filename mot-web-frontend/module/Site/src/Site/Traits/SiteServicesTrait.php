<?php

namespace Site\Traits;

use DvsaClient\MapperFactory;

trait SiteServicesTrait
{
    /**
     * @return MapperFactory
     */
    protected function getMapperFactory()
    {
        return $this->getServiceLocator()->get(MapperFactory::class);
    }
}
