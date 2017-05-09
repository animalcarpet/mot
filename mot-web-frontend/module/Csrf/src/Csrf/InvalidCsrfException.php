<?php

namespace Csrf;

/**
 * Exception thrown when an invalid csrf token or no token has been found in the request payload.
 */
class InvalidCsrfException extends \Exception
{
}
