<?php

namespace HtaccessCapabilityTester;

interface HTTPRequesterInterface
{
    /**
     * Make a HTTP request to a URL.
     *
     * @return  HTTPResponse  A HTTPResponse object, which simply contains body and status code.
     */
    public function makeHTTPRequest($url);
}
