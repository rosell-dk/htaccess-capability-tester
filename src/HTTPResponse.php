<?php

namespace HtaccessCapabilityTester;

/**
 * Class for holding properties of a HTTPResponse
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since 0.7
 */
class HTTPResponse
{

    /* @var string  the body of the response */
    public $body;

    /* @var string  the status code of the response */
    public $statusCode;

    /* @var array  the response headers */
    public $headers;

    /**
     * Constructor.
     *
     * @param  string  $body
     * @param  string  $statusCode
     * @param  array   $headers
     *
     * @return void
     */
    public function __construct($body, $statusCode, $headers)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
}
