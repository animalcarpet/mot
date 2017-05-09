<?php

namespace DvsaMotTest\Action;

use Core\Action\ActionResultLayout;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaMotTest\Form\VehicleSearch\DuplicateCertificateVinSearchForm;

class DuplicateCertificateSearchByVinAction extends AbstractDuplicateCertificateSearchAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Search by VIN';
    const TEMPLATE_NAME = 'dvsa-mot-test/vehicle-search/search-for-duplicate-by-vin.phtml';

    protected function getForm()
    {
        return new DuplicateCertificateVinSearchForm();
    }

    protected function setAdditionalLayoutProperties(ActionResultLayout $layout)
    {
        return $layout->setPageTitle(self::PAGE_TITLE);
    }

    protected function getTemplate()
    {
        return self::TEMPLATE_NAME;
    }
}
