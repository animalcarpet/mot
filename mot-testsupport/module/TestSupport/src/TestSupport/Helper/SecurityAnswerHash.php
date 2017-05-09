<?php

namespace TestSupport\Helper;

use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommon\Crypt\Hash\HashFunctionInterface;

/**
 * Designed to hash answers to security questions. The answer can be
 * no longer than 72 characters.
 *
 * Class SecurityAnswerHash
 */
class SecurityAnswerHash implements HashFunctionInterface
{
    private $baseFunction;

    public function __construct()
    {
        $this->baseFunction = new BCryptHashFunction();
    }

    public function hash($answer)
    {
        $canonicalized = $this->canonicalizeAnswer($answer);
        $hashed = $this->baseFunction->hash($canonicalized);

        return $hashed;
    }

    private function canonicalizeAnswer($answer)
    {
        $answer = preg_replace('/\s+/', '', $answer);
        $answer = strtoupper($answer);

        return $answer;
    }

    public function verify($secret, $hash)
    {
        $canonicalized = $this->canonicalizeAnswer($secret);

        return $this->baseFunction->verify($canonicalized, $hash);
    }

    /**
     * @param \DvsaCommon\Crypt\Hash\HashFunctionInterface $hashFunction
     *
     * @return $this
     */
    public function setBaseFunction(HashFunctionInterface $hashFunction)
    {
        $this->baseFunction = $hashFunction;

        return $this;
    }
}
