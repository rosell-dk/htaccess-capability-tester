<?php

namespace HtaccessCapabilityTester;

/**
 * Class for holding properties of a HTTPResponse
 *
 * @package    HtaccessCapabilityTester
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since the beginning
 */
class HTTPResponse
{

    /* @var string  the body of the response */
    public $body;

    /* @var string  the status code of the response */
    public $statusCode;

    /**
     * Constructor.
     *
     * @param  string  $body
     * @param  string  $statusCode
     *
     * @return void
     */
    public function __construct($body, $statusCode)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
    }
}
