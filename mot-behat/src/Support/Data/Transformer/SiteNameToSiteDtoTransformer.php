<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;
use DvsaCommon\Dto\Site\SiteDto;

trait SiteNameToSiteDtoTransformer
{
    /**
     * @Transform :site
     * @Transform :site1
     * @Transform :site2
     */
    public function castSiteNameToSiteDto($siteName)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $siteName;
        }

        $collection = SharedDataCollection::get(SiteDto::class);

        return $collection->get($siteName);
    }
}
