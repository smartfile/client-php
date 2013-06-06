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
 *  API client that uses OAuth tokens. Layers a more complex form of
 *  authentication useful for 3rd party access on top of the base Client.
 *
 * @category  Web_Services
 * @package   SmartFile_Client
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @link      http://pear.php.net/package/SmartFile
 */
Class Service_SmartFile_OAuthClient extends Service_SmartFile_Client
{


    // {{{ private properties
    /**
     * SmartFile Client Token
     * You get it when you register your application.
     * @var string
     */
    private $_client_token = null;

    /**
     * SmartFile Client Secret
     * You get it when you register your application.
     * @var string
     */
    private $_client_secret = null;

    /**
     * SmartFile Access Token
     * This is given to you after you call getAccessToken() and should be saved by your application.
     * @var string
     */
    private $_access_token = null;

    /**
     * SmartFile Access Secret
     * This is given to you after you call getAccessToken() and should be saved by your application.
     * @var string
     */
    private $_access_secret = null;

    /**
     * nonce char list
     * @var string
     */
    private $_nonce_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // }}}

    // {{{ __construct()

    /**
     * Private constructor. Set the oauth token and secret once
     * when you instantiate this class.
     *
     * @param string $client_token  Client OAuth Token
     * @param string $client_secret Client OAuth Secret
     * @param string $client_token  OAuth Access Token
     * @param string $client_secret OAuth Access Secret
     *
     * @return null
     */
    function __construct($client_token=null, $client_secret=null, $access_token=null, $access_secret=null)
    {
        $this->_client_token = $client_token;
        $this->_client_secret = $client_secret;
        $this->_access_token = $access_token;
        $this->_access_secret = $access_secret;
    }

    // }}}

    /**
     * Generate a random string of x chars
     *
     * @param int $length  Length of resultant string
     *
     * @return string
     */
    private function _genNonce($length=32)
    {
        $result = '';
        $cLength = strlen($this->_nonce_chars);
        for ($i=0; $i < $length; $i++)
        {
            $rnum = rand(0,$cLength);
            $result .= substr($this->_nonce_chars, $rnum, 1);
        }
        return $result;
    }

    /**
     * Override the parent class to add OAuth Authentication here.
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
        return parent::doRequest($uri, $method, $data, $extra_headers);
    }

    /**
     * The first step of the OAuth workflow.
     *
     * @param string $callback Callback to call once complete
     *
     * @return string
     */
    public function getRequestToken($callback=null)
    {
        $uri = 'http://app.smartfile.com/oauth/request_token/';
        $data = array(
            'oauth_version' => '1.0',
            'oauth_nonce' => $this->_genNonce(),
            'oauth_timestamp' => time(),
            'oauth_consumer_key' => $this->_client_token,
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_signature' => $this->_client_secret . '&'
        );
        $result = parent::doRequest($uri, 'post', $data, '');
        $sep = strpos($result, "\r\n\r\n");
        $result = substr($result, $sep + 4);
        $result = trim($result);
        if ($result == 'Could not verify OAuth request.') {
            throw new Service_SmartFile_APIException($result);
        }
        $result = parse_str($result);
        return $result;
    }

    /**
     * The second step of the OAuth workflow.
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {


    }

    /**
     * The final step of the OAuth workflow. After this the client can make
     * API calls.
     *
     * @return array
     */
    public function getAccessToken()
    {



    }

}

?>