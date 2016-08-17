<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;
use DvsaCommon\Dto\Organisation\OrganisationDto;

trait AeNameToOrganisationDtoTransformer
{
    /**
     * @Transform :ae
     */
    public function castAeNameToOrganisationDto($aeName)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $aeName;
        }

        $collection = SharedDataCollection::get(OrganisationDto::class);

        return $collection->get($aeName);
    }
}
