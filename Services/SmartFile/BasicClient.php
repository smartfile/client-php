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

require_once 'Client.php';

/**
 *  Basic Authentication API client, handles communication, retry, versioning etc.
 *
 * @category  Web_Services
 * @package   SmartFile_Client
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @link      http://pear.php.net/package/SmartFile
 */
Class Service_SmartFile_BasicClient extends Service_SmartFile_Client
{

    // {{{ private properties
    /**
     * SmartFile Api Key
     * @var string
     */
    private $api_key = '';

    /**
     * SmartFile Api password
     * @var string
     */
    private $api_pwd = '';

    // }}}
    // {{{ __construct()

    /**
     * Private constructor. Set the api key and pass once
     * when you instantiate this class.
     */
    function __construct($key, $pass) {
        $this->api_key = $key;
        $this->api_pwd = $pass;
    }

    // }}}

    /**
     * Override the parent class to add Basic Authentication here.
     * Actually makes the HTTP request
     *
     * @param string $uri           Url of the Request
     * @param string $method        Http Method
     * @param array  $data          Http Parameters
     * @param string $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    public function doRequest($uri, $method, $data=null, $extra_headers='')
    {
        $auth = base64_encode($this->api_key . ":" . $this->api_pwd);
        $extra_headers = $extra_headers . "Authorization: Basic " . $auth . "\r\n";
        return parent::doRequest($uri, $method, $data, $extra_headers);

    }
}

?>