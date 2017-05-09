<?php

namespace DvsaMotTest\Action;

use Core\Action\ActionResultLayout;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaMotTest\Form\VehicleSearch\DuplicateCertificateRegistrationSearchForm;

class DuplicateCertificateSearchByRegistrationAction extends AbstractDuplicateCertificateSearchAction implements AutoWireableInterface
{
    const TEMPLATE_NAME = 'dvsa-mot-test/vehicle-search/search-for-duplicate-by-registration.phtml';
    const PAGE_TITLE = 'Search by registration mark';

    protected function getForm()
    {
        return new DuplicateCertificateRegistrationSearchForm();
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
