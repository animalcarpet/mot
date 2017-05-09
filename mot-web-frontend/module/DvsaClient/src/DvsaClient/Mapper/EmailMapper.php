<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\Email;

/**
 * Class EmailMapper.
 */
class EmailMapper extends AutoMapper
{
    protected $entityClass = Email::class;

    /**
     * @param $data
     *
     * @return Email[]
     */
    public function hydrateArray($data)
    {
        return parent::doHydration($data);
    }
}
