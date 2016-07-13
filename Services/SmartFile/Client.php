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
     * @param array  $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    protected function doRequest($uri, $method, $data=null,
                                 $extra_headers=array())
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

        // Convert data, which could have a resource, into string to send.
        if (is_null($data)) {
            $data = '';
        } else {
            $aryData = array_values($data);
            if (is_resource($aryData[0])) {
                $boundary = "----------------------------" . uniqid();
                $content_type = "multipart/form-data; boundary=$boundary";
                $filename = array_keys($data)[0];
                $rh = array_values($data)[0];
                $data = "--$boundary\r\n" .
                    "Content-Disposition: form-data; " .
                    "name=\"$filename\"; filename=\"$filename\"\r\n" .
                    "Content-Type: application/octet-stream\r\n\r\n" .
                    fread($rh, fstat($rh)['size']) .
                    "\r\n--$boundary--";
            } else {
                $data = http_build_query($data);
                // SmartFile API does not use the [0], [1], [2] style parameters
                $data = preg_replace('/%5B[0-9]+%5D/simU', '', $data);
            }
        }

        // Add a default content type and convert headers into string.
        if (!array_key_exists('content-type',
                              array_change_key_case($extra_headers))) {
            $extra_headers['Content-Type'] =
                isset($content_type) ? $content_type :
                'application/x-www-form-urlencoded';
        }
        $extra_headers_str = "";
        foreach ($extra_headers as $key => $value) {
            $extra_headers_str .= $key . ": " . $value . "\r\n";
        }

        $getdata_str = ((strtolower($method) == 'get' && count($data)) ? '?' : '');
        if (strtolower($method) == 'get') {
            $getdata_str .= $data;
        }


        $fp = @fsockopen($url_parts['host'], $port, $errno, $errstr, 30);
        if (!$fp) {
            throw new Service_SmartFile_RequestException(
                'Error contacting Server: ' . $errstr
            );
        }
        fputs(
            $fp,  strtoupper($method) . ' ' . $url_parts['path']. $getdata_str . " HTTP/1.1\r\n" .
            'Host: ' . $host_header . "\r\n" .
            "User-Agent: SmartFile PHP API client v2.1\r\n" .
            (strtolower($method) == 'post' ? 'Content-Length: ' . strlen($data) . "\r\n" : '') .
            $extra_headers_str .
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
     * @param array  $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    private function _request($uri, $method, $data=null, $extra_headers=array())
    {
        $response = $this->doRequest($uri, $method, $data, $extra_headers);

        // Get Status from headers:
        $sep = strpos($response, "\r\n");
        $headers = substr($response, 0, $sep);
        list($ignored, $http_status, $ignored) = explode(' ', $headers);

        // $response = $this->getBody($response);

        $method = strtolower($method);
        if (($method == 'get' && $http_status != 200)
            || ($method == 'post' && $http_status != 200 && $http_status != 201)
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
     * @param array  $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    public function get($endpoint, $data=null, $extra_headers=array())
    {
        return $this->_request($endpoint, 'get', $data, $extra_headers);
    }

    /**
     * Public wrapper for DOWNLOAD requests
     *
     * @param string $file_to_be_downloaded file client will download
     *
     * @return array
     */
    public function download($file_to_be_downloaded)
    {
        $response = $this->doRequest('/path/data/' . $file_to_be_downloaded, 'get');
        $removeheaders = $this->getBody($response);
        return file_put_contents($file_to_be_downloaded, $removeheaders);
    }

    /**
     * Public wrapper for PUT requests
     *
     * @param string $endpoint URI of endpoint
     * @param array  $data     PUT data
     * @param array  $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    public function put($endpoint, $data=null, $extra_headers=array())
    {
        return $this->_request($endpoint, 'put', $data, $extra_headers);
    }

    /**
     * Public wrapper for POST requests
     *
     * @param string $endpoint URI of endpoint
     * @param array  $data     POST data
     * @param array  $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    public function post($endpoint, $data=null, $extra_headers=array())
    {
        return $this->_request($endpoint, 'post', $data, $extra_headers);
    }

    /**
     * Public wrapper for D requests
     *
     * @param string $file_to_be_uploaded file client will upload
     *
     * @return array
     */
    public function upload($file_to_be_uploaded)
    {
        $rh = fopen($file_to_be_uploaded, "rb");
        $this->post("/path/data/", array($file_to_be_uploaded => $rh));
        fclose($rh);
        return $this;
    }

    /**
     * Public wrapper for DELETE requests
     *
     * @param string $file_to_be_deleted file client will delete
     *
     * @return array
     */
    public function remove($file_to_be_deleted)
    {
        $this->post('/path/oper/remove/', array('path' => $file_to_be_deleted));
        return $this;

    }

    /**
     * Public wrapper for MOVE requests
     *
     * @param string $file_to_be_moved file client will download
     * @param string $destination_folder the folder the client is moving their file to
     *
     * @return array
     */
    public function move($file_to_be_moved, $destination_folder)
    {
        $this->post('/path/oper/move/', array('src' => $file_to_be_moved, 'dst' => $destination_folder));
        return $this;
    }
}

?>
