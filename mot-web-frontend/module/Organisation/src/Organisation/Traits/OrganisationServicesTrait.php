<?php

namespace Organisation\Traits;

use DvsaClient\MapperFactory;

trait OrganisationServicesTrait
{
    /**
     * @return MapperFactory
     */
    protected function getMapperFactory()
    {
        return $this->getServiceLocator()->get(MapperFactory::class);
    }
}
