<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel\Exception;

use RuntimeException;

/**
 * Thrown when an IdentifiedDefect can not be found in the API.
 */
class IdentifiedDefectNotFoundException extends RuntimeException
{
    /**
     * IdentifiedDefectNotFoundException constructor.
     *
     * @param int $identifiedDefectId
     */
    public function __construct($identifiedDefectId)
    {
        parent::__construct(sprintf('Unable to find IdentifiedDefect with id "%d".', $identifiedDefectId));
    }
}
