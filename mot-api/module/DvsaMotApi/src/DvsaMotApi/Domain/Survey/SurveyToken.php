<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Domain\Survey;

use Ramsey\Uuid\Uuid;

/**
 * A Token uniquely identifies a GDS Satisfaction Survey presented to a user.
 */
class SurveyToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * SurveyToken constructor.
     */
    public function __construct()
    {
        $this->token = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Check if a string is a valid UUID.
     *
     * @param string $uuid The string UUID to test
     *
     * @return bool
     */
    public static function isValid($uuid)
    {
        if (!is_string($uuid)) {
            return false;
        }

        return Uuid::isValid($uuid);
    }
}
