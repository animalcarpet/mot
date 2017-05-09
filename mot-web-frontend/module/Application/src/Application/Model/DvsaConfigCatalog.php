<?php

namespace Application\Model;

/**

 * This model encapsulates all those settings that MAY change in the future
 * but with such a low frequency of update that hard-coding it in a Model is
 * the simplest thing that will work.
 *
 * This is not to be mixed up with the CatalogService
 */
class DvsaConfigCatalog
{
    public function __construct($restClient)
    {
        // not used any more
    }

    /**
     * TODO: Migrate to DB lookup table when slack time permits.
     *
     * @return array of RFR-is for Items not tested in Class 2 vehicles
     */
    public function getClass2RfrsNotTested()
    {
        return [1020, 1021, 1022];
    }

    /**
     * TODO: Migrate to DB lookup table when slack time permits.
     *
     * @return array of RFR-is for Items not tested in Class 4 vehicles
     */
    public function getClass4RfrsNotTested()
    {
        return [970, 972, 8566, 8567, 8568, 8569];
    }

    /**
     * @param int $id contains the RFR internal identifier,
     *
     * @return bool is the RFR Id is an "item not tested"
     *
     * @throws \Exception if $id cannot be treated as a numeric value
     */
    public function isItemNotTested($id)
    {
        // Manual RFR's get a null rfr id
        if (is_null($id)) {
            return false;
        }

        if (!is_numeric($id)) {
            throw new \Exception('RFR id must be numeric');
        }

        return in_array((int) $id, $this->getClass2RfrsNotTested())
        || in_array((int) $id, $this->getClass4RfrsNotTested());
    }
}
