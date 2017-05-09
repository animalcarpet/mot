<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\Phone;

/**
 * Class PhoneMapper.
 */
class PhoneMapper extends AutoMapper
{
    protected $entityClass = Phone::class;

    /**
     * @param $data
     *
     * @return Phone[]
     */
    public function hydrateArray($data)
    {
        return parent::doHydration($data);
    }
}
