<?php

namespace HtaccessCapabilityTester;

class SimpleHttpRequestor implements iHTTPRequestor
{
    /**
     * Make a HTTP request to a URL.
     *
     * @return  string  The response text
     */
    public function makeHTTPRequest($url)
    {
        $text = file_get_contents($url);
        // var_dump($http_response_header);
        return $text;
    }
}
