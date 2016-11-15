<?php

namespace Dvsa\Mot\Behat\Support;

class ResponseBody implements \ArrayAccess
{
    /**
     * @var array
     */
    private $body;

    /**
     * @param array $body
     */
    public function __construct(array $body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->body, JSON_PRETTY_PRINT);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->body['data'];
    }

    public function getErrors()
    {
        return $this->body['errors'];
    }

    public function getErrorMessages()
    {
        $errors = $this->getErrors();
        $messages = [];
        if (array_key_exists("message", $errors)) {
            $messages[] = $errors["message"];
        } else {
            foreach ($errors as $error) {
                if (array_key_exists("message", $error)) {
                    $messages[] = $error["message"];
                }
            }
        }

        return $messages;
    }

    public function getErrorMessagesAsString()
    {
        return join("; ", $this->getErrorMessages());
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->body[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->body)) {
            throw new \LogicException(sprintf('Index "%s" not found in %s', $offset, var_export($this->body, true)));
        }

        if (is_array($this->body[$offset])) {
            return new self($this->body[$offset]);
        }

        return $this->body[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Response body modifications are not allowed');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Response body modifications are not allowed');
    }
}