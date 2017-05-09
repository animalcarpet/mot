<?php

namespace Organisation\UpdateAeProperty;

use Core\Action\RedirectToRoute;
use Core\Routing\AeRouteList;
use Core\TwoStepForm\TwoStepProcessInterface;
use Core\ViewModel\Gds\Table\GdsTable;

abstract class AbstractTwoStepAeProcess extends AbstractSingleStepAeProcess implements TwoStepProcessInterface
{
    public function buildReviewStepViewModel($formUuid, $formData, GdsTable $table)
    {
        return new UpdateAePropertyReviewViewModel($this->context->getAeId(), $this->context->getPropertyName(),
            $formUuid, $this->getReviewPageButtonText(), $formData, $table);
    }

    public function redirectToReviewPage($formUuid)
    {
        return new RedirectToRoute(AeRouteList::AE_EDIT_PROPERTY_REVIEW,
            [
                'id' => $this->context->getAeId(),
                'propertyName' => $this->context->getPropertyName(),
                'formUuid' => $formUuid,
            ]
        );
    }

    public function getSessionStoreKey()
    {
        return sprintf('%s/%s/%s',
            'UPDATE_AE_PROPERTY_FORM',
            $this->context->getAeId(),
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
