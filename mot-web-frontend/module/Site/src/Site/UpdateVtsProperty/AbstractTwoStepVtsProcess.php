<?php

namespace Site\UpdateVtsProperty;

use Core\Action\RedirectToRoute;
use Core\Routing\VtsRouteList;
use Core\TwoStepForm\TwoStepProcessInterface;
use Core\ViewModel\Gds\Table\GdsTable;

abstract class AbstractTwoStepVtsProcess extends AbstractSingleStepVtsProcess implements TwoStepProcessInterface
{
    public function buildReviewStepViewModel($formUuid, $formData, GdsTable $table)
    {
        return new UpdateVtsPropertyReviewViewModel($this->context->getVtsId(), $this->context->getPropertyName(), $formUuid, $this->getReviewPageButtonText(), $formData, $table);
    }

    public function redirectToReviewPage($formUuid)
    {
        return new RedirectToRoute(VtsRouteList::VTS_EDIT_PROPERTY_REVIEW,
            ['id' => $this->context->getVtsId(), 'propertyName' => $this->context->getPropertyName(), 'formUuid' => $formUuid]
        );
    }

    public function getSessionStoreKey()
    {
        return sprintf(
            '%s/%s/%s',
            'UPDATE_VTS_PROPERTY_FORM',
            $this->context->getVtsId(),
            $this->context->getPropertyName()
        );
    }

    public function hasConfirmationPage()
    {
        return false;
    }

    public function redirectToConfirmationPage()
    {
    }

    public function populateConfirmationPageVariables()
    {
    }
}
