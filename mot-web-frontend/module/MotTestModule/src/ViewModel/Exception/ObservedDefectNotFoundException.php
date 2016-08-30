<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel\Exception;

use RuntimeException;

/**
 * Thrown when an ObservedDefect can not be found in the API.
 */
class ObservedDefectNotFoundException extends RuntimeException
{
    /**
     * ObservedDefectNotFoundException constructor.
     *
     * @param int $observedDefectId
     */
    public function __construct($observedDefectId)
    {
        parent::__construct(sprintf('Unable to find ObservedDefect with id "%d".', $observedDefectId));
    }
}
