<?php

namespace DvsaMotTest\Controller;

use Core\Controller\AbstractAuthActionController;

/**
 * Class VehicleDictionaryController.
 */
class VehicleDictionaryController extends AbstractAuthActionController
{
    const BASE_VEH_DICTIONARY_URL = 'vehicle-dictionary';

    /**
     * @return mixed
     */
    public function findMakeAction()
    {
        $query = $this->params()->fromQuery('query');

        $params = ['searchType' => 'make', 'searchTerm' => $query];
        $makes = $this->getRestClient()->getWithParams(self::BASE_VEH_DICTIONARY_URL, $params)['data'];

        return $this->ajaxResponse()->ok($makes);
    }

    /**
     * @return mixed
     */
    public function findModelAction()
    {
        $query = $this->params()->fromQuery('query');

        $make = $this->params()->fromQuery('make');
        $params = ['searchType' => 'model', 'searchTerm' => $query, 'make' => $make];
        $makeModels = $this->getRestClient()->getWithParams(self::BASE_VEH_DICTIONARY_URL, $params)['data'];

        return $this->ajaxResponse()->ok($makeModels);
    }
}
