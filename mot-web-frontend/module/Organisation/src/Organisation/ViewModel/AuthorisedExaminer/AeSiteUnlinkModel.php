<?php

namespace Organisation\ViewModel\AuthorisedExaminer;

use DvsaCommon\Dto\Site\SiteDto;

/**
 * Class AeSiteUnlinkModel.
 */
class AeSiteUnlinkModel extends AeFormViewModel
{
    /**
     * @var SiteDto
     */
    private $site;

    /**
     * @return SiteDto
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return $this
     */
    public function setSite(SiteDto $site)
    {
        $this->site = $site;

        return $this;
    }
}
