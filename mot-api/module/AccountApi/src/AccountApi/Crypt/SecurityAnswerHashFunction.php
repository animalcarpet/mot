<?php

namespace AccountApi\Crypt;

use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommon\Crypt\Hash\HashFunctionInterface;

/**
 * Designed to hash answers to security questions. The answer can be
 * no longer than 72 characters.
 *
 * Class SecurityAnswerHashFunction
 */
class SecurityAnswerHashFunction implements HashFunctionInterface
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
        $answer = $this->stripAllWhitespace($answer);
        $answer = $this->capitalize($answer);

        return $answer;
    }

    private function stripAllWhitespace($answer)
    {
        return preg_replace('/\s+/', '', $answer);
    }

    private function capitalize($answer)
    {
        return strtoupper($answer);
    }

    /**
     * @param string $secret
     * @param string $hash
     *
     * @return bool
     */
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
