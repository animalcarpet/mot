<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use DvsaCommon\Dto\Organisation\OrganisationDto;

trait AeNameToOrganisationDtoTransformer
{
    /**
     * @Transform :ae
     */
    public function castAeNameToOrganisationDto($aeName)
    {
        $collection = SharedDataCollection::get(OrganisationDto::class);

        return $collection->get($aeName);
    }
}
