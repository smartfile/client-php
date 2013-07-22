<?php
/**
 * SmartFile PHP SDK
 *
 * PHP version 5
 *
 * LICENSE: The MIT License (MIT)
 *
 * Copyright Copyright (c) 2012, SmartFile
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Web_Services
 * @package   SmartFile
 * @author    Ben Timby <btimby@gmail.com>
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @version   GIT: $Id$
 * @link      http://pear.php.net/package/SmartFile
 * @since     File available since Release 2.1
 */

require_once 'Exception.php';

/**
 *  Base API client, handles communication, retry, versioning etc.
 *
 * @category  Web_Services
 * @package   SmartFile_Client
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @link      http://pear.php.net/package/SmartFile
 */
class Service_SmartFile_Client
{

    // {{{ public properties

    /**
     * Smartfile API URL.
     * @var string
     */
    public $api_base_url= 'https://app.smartfile.com/api/2';

    // }}}

    /**
     * This function checks for chunked http response
     * and returns body of response.
     *
     * @param string $response Http Response to split
     *
     * @return string
     */
    protected function getBody($response)
    {
        // strip the HTTP headers:
        $sep = strpos($response, "\r\n\r\n");
        $headers = substr($response, 0, $sep);
        $sep = strpos($response, "\r\n\r\n");
        $response = substr($response, $sep + 4);
        if (stristr($headers, 'Transfer-Encoding: chunked')) {
            $response = $this->decodeChunked($response);
        }
        $response = trim($response);
        return $response;
    }

    /**
     * This function decodes a chunked http response
     *
     * @param string $str Http Response to decode
     *
     * @return string
     */
    protected function decodeChunked($str)
    {
        for ($res = ''; !empty($str); $str = trim($str)) {
            $pos = strpos($str, "\r\n");
            $len = hexdec(substr($str, 0, $pos));
            $res.= substr($str, $pos + 2, $len);
            $str = substr($str, $pos + 2 + $len);
        }
        return $res;
    }

    /**
     * This function performs an HTTP request and parses the response.
     * It raises an exception if the server cannot be contacted or if
     * the server returns a status other than 201. Any status other
     * that 201 results in the exception receiving the HTTP status in
     * addition to the returned error message.
     *
     * @param string $uri           Url of the Request
     * @param string $method        Http Method
     * @param array  $data          Http Parameters
     * @param string $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    protected function doRequest($uri, $method, $data=null, $extra_headers='')
    {
        if (substr($uri, 0, 4) == 'http') {
            $url = $uri;
        } else {
            $url = $this->api_base_url . $uri;
        }
        $url_parts = parse_url($url);
        $host_header = $url_parts['host'];
        if (array_key_exists('port', $url_parts)) {
            $port = $url_parts['port'];
            $host_header .= ":" . $port;
        } else {
            $port = 80;
        }
        if (!is_null($data)) {
            $data = http_build_query($data);
        } else {
            $data = '';
        }

        // We use fsockopen to perform our HTTP request as that is the
        // most compatible way of doing so. This method should work on
        // just about any PHP installation and does not require cURL
        // or any other HTTP extensions.
        $fp = @fsockopen($url_parts['host'], $port, $errno, $errstr, 30);
        if (!$fp) {
            throw new Service_SmartFile_RequestException(
                'Error contacting Server: ' . $errstr
            );
        }
        fputs(
            $fp,  strtoupper($method) . ' ' . $url_parts['path'] . " HTTP/1.1\r\n" .
            'Host: ' . $host_header . "\r\n" .
            "User-Agent: SmartFile PHP API client v2.1\r\n" .
            "Content-Type: application/x-www-form-urlencoded\r\n" .
            'Content-Length: ' . strlen($data) . "\r\n" .
            $extra_headers .
            "Connection: close\r\n\r\n"
        );
        if (strtolower($method) == 'post') {
            fputs($fp, $data);
        }
        $response = '';
        while ($line = fread($fp, 4096)) {
            $response .= $line;
        }
        fclose($fp);
        return $response;
    }

    /**
     * Handles retrying failed requests and error handling.
     *
     * @param string $uri    URI of endpoint
     * @param string $method HTTP method
     * @param array  $data   HTTP request data
     *
     * @return array
     */
    private function _request($uri, $method, $data=null)
    {
        $response = $this->doRequest($uri, $method, $data);

        // Get Status from headers:
        $sep = strpos($response, "\r\n");
        $headers = substr($response, 0, $sep);
        list($ignored, $http_status, $ignored) = split(' ', $headers);

        $response = $this->getBody($response);

        $method = strtolower($method);
        if (($method == 'get' && $http_status != 200)
            || ($method == 'post' && $http_status != 201)
            || ($method == 'put' && $http_status != 200)
            || ($method == 'delete' && $http_status != 204)
        ) {
            // Non-success status, so parse out the message and inform the caller
            // via an Exception.
            throw new Service_SmartFile_ResponseException($http_status, $response);
        }
        $response = json_decode($response, true);
        return $response;
    }

    /**
     * Public wrapper for GET requests
     *
     * @param string $endpoint URI of endpoint
     * @param array  $data     Get params
     *
     * @return array
     */
    public function get($endpoint, $data=null)
    {
        return $this->_request($endpoint, 'get', $data);
    }

    /**
     * Public wrapper for PUT requests
     *
     * @param string $endpoint URI of endpoint
     * @param array  $data     PUT data
     *
     * @return array
     */
    public function put($endpoint, $data=null)
    {
        return $this->_request($endpoint, 'put', $data);
    }

    /**
     * Public wrapper for POST requests
     *
     * @param string $endpoint URI of endpoint
     * @param array  $data     POST data
     *
     * @return array
     */
    public function post($endpoint, $data=null)
    {
        return $this->_request($endpoint, 'post', $data);
    }

    /**
     * Public wrapper for DELETE requests
     *
     * @param string $endpoint URI of endpoint
     * @param array  $data     DELETE data
     *
     * @return array
     */
    public function delete($endpoint, $data=null)
    {
        return $this->_request($endpoint, 'delete', $data);
    }
}

?>