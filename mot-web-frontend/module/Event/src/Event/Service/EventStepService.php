<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Service;

use Core\Service\StepService;

/**
 * EventStep Service.
 */
class EventStepService extends StepService
{
    /**
     * @param string $entityType ae|site|person
     * @param int    $entityId   id of the entity
     */
    public function injectParamsIntoSteps($entityType, $entityId)
    {
        foreach ($this->steps as $step) {
            $step->setEntityType($entityType);
            $step->setEntityId($entityId);
        }

        $this->rewind();
    }
}
