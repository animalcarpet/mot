<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\SecurityQuestionSet;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\UrlBuilder\UrlBuilder;

class SecurityQuestionMapper extends DtoMapper
{
    /**
     * @return SecurityQuestionDto[]
     */
    public function fetchAll()
    {
        $url = (new UrlBuilder())->securityQuestion()->toString();

        return $this->get($url);
    }

    /**
     * @return SecurityQuestionSet
     */
    public function fetchAllGroupedAndOrdered()
    {
        return new SecurityQuestionSet($this->fetchAll());
    }
}
