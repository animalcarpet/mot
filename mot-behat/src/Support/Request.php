<?php

namespace Dvsa\Mot\Behat\Support;

class Request
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var null|string
     */
    private $body;

    /**
     * @param string      $method
     * @param string      $uri
     * @param array       $headers
     * @param string|null $body
     */
    public function __construct($method, $uri, array $headers = [], $body = null)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $headers = $this->getHeadersAsString();
        $uri = strpos($this->uri, '/') === 0 ? $this->uri : '/'.$this->uri;
        $body = $this->prettifyBody();

        return <<<MESSAGE
$this->method $uri
$headers

$body
MESSAGE;
    }

    /**
     * @return string
     */
    private function getHeadersAsString()
    {
        return implode("\n", array_map(
            function ($header, $value) {
                return $header.': '.(is_array($value) ? implode(',', $value) : $value);
            },
            array_keys($this->headers),
            $this->headers
        ));
    }

    private function prettifyBody()
    {
        if(!is_null($this->body) && !is_null(json_decode($this->body))){
            return json_encode(json_decode($this->body), JSON_PRETTY_PRINT);
        } else {
            return $this->body;
        }
    }
}
