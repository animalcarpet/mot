<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaMotApi\Validation;

use Zend\Validator\Callback;
use Zend\Validator\Exception;

class ContingencyCodeValidator extends Callback
{
    const INVALID = 'invalidContingencyCode';

    protected $messageTemplates = [
        self::INVALID => 'must be a valid contingency code',
    ];

    /**
     * Returns true if and only if the set callback returns
     * for the provided $value.
     *
     * @param mixed $value
     * @param mixed $context Additional context to provide to the callback
     *
     * @return bool
     *
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $options = $this->getCallbackOptions();
        $callback = $this->getCallback();
        if (empty($callback)) {
            throw new Exception\InvalidArgumentException('No callback given');
        }
        $args = [$value];
        if (empty($options) && !empty($context)) {
            $args[] = $context;
        }
        if (!empty($options) && empty($context)) {
            $args = array_merge($args, $options);
        }
        if (!empty($options) && !empty($context)) {
            $args[] = $context;
            $args = array_merge($args, $options);
        }

        if (!call_user_func_array($callback, $args)) {
            $this->error(self::INVALID);

            return false;
        }

        return true;
    }
}
