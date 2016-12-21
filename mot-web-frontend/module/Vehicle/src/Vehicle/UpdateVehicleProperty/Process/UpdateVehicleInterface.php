<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\AbstractRedirectActionResult;
use Core\TwoStepForm\SingleStepProcessInterface;

interface UpdateVehicleInterface extends SingleStepProcessInterface
{
    const PAGE_SUBTITLE_UPDATE_DURING_TEST = "Change vehicle record";

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartUnderTestPage();
}