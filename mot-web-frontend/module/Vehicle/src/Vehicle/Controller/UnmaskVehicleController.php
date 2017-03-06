<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Vehicle\Controller;

use Core\Routing\VehicleRoutes;
use DvsaCommon\Auth\PermissionInSystem;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class UnmaskVehicleController extends BaseMaskUnmaskVehicleController
{
    /**
     * @return array|Response|ViewModel
     */
    public function unmaskAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES);

        $obfuscatedVehicleId = (string) $this->params('id');
        $vehicleId = $this->deobfuscateVehicleId($obfuscatedVehicleId);
        if ($vehicleId == 0) {
            return $this->notFoundAction();
        }

        $this->enableGdsLayout('Unmask this vehicle', 'Vehicle');
        $breadcrumbs = $this->getBreadcrumbs($obfuscatedVehicleId, 'Unmask the vehicle');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->vehicleService->unmaskDvsaVehicle($vehicleId);

            return $this->redirect()->toUrl(VehicleRoutes::of($this->url)->vehicleUnmaskedSuccessfully($obfuscatedVehicleId));
        }

        return $this->createViewModel('vehicle/enforcement/unmask.twig', [
            'breadcrumbs' => $breadcrumbs,
            'obfuscatedVehicleId' => $obfuscatedVehicleId,
        ]);
    }

    /**
     * @return array|ViewModel
     */
    public function unmaskedSuccessfullyAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES);

        $obfuscatedVehicleId = (string) $this->params('id');
        $vehicleId = $this->deobfuscateVehicleId($obfuscatedVehicleId);
        if ($vehicleId == 0) {
            return $this->notFoundAction();
        }

        $this->enableGdsLayout('', '');
        $breadcrumbs = $this->getBreadcrumbs($obfuscatedVehicleId, 'Unmask the vehicle');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $this->createViewModel('vehicle/enforcement/unmasked-successfully.twig', [
            'breadcrumbs' => $breadcrumbs,
            'obfuscatedVehicleId' => $obfuscatedVehicleId,
        ]);
    }
}
