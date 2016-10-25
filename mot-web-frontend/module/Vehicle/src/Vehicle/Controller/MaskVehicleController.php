<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Vehicle\Controller;

use Core\Routing\VehicleRoutes;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class MaskVehicleController extends BaseMaskUnmaskVehicleController
{
    /**
     * @return array|Response|ViewModel
     */
    public function maskAction()
    {
        if (true !== $this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER)) {
            return $this->notFoundAction();
        }

        $this->authorisationService->assertGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES);

        $obfuscatedVehicleId = (string) $this->params('id');
        $vehicleId = $this->deobfuscateVehicleId($obfuscatedVehicleId);
        if ($vehicleId == 0) {
            return $this->notFoundAction();
        }

        $this->enableGdsLayout('Mask this vehicle', 'Vehicle');
        $breadcrumbs = $this->getBreadcrumbs($obfuscatedVehicleId, 'Mask the vehicle');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->vehicleService->maskDvsaVehicle($vehicleId);

            return $this->redirect()->toUrl(VehicleRoutes::of($this->url)->vehicleMaskedSuccessfully($obfuscatedVehicleId));
        }

        return $this->createViewModel('vehicle/enforcement/mask.twig', [
            'breadcrumbs' => $breadcrumbs,
            'obfuscatedVehicleId' => $obfuscatedVehicleId,
        ]);
    }

    /**
     * @return array|ViewModel
     */
    public function maskedSuccessfullyAction()
    {
        if (true !== $this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER)) {
            return $this->notFoundAction();
        }

        $this->authorisationService->assertGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES);

        $obfuscatedVehicleId = (string) $this->params('id');
        $vehicleId = $this->deobfuscateVehicleId($obfuscatedVehicleId);
        if ($vehicleId == 0) {
            return $this->notFoundAction();
        }

        $this->enableGdsLayout('', '');
        $breadcrumbs = $this->getBreadcrumbs($obfuscatedVehicleId, 'Mask the vehicle');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $this->createViewModel('vehicle/enforcement/masked-successfully.twig', [
            'breadcrumbs' => $breadcrumbs,
            'obfuscatedVehicleId' => $obfuscatedVehicleId,
        ]);
    }
}
