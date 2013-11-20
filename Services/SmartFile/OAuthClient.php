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
    // {{{ public properties

    /**
     * Smartfile API URL.
     * @var string
     */
    public $oauth_base_url= 'https://app.smartfile.com';

    // }}}

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
     * SmartFile Request Token
     * This is given to you after you call getRequestToken() and should be saved
     * by your application.
     * @var string
     */
    private $_request_token = null;

    /**
     * SmartFile Request Secret
     * This is given to you after you call getRequestToken() and should be saved
     * by your application.
     * @var string
     */
    private $_request_secret = null;

    /**
     * SmartFile Access Token
     * This is given to you after you call getAccessToken() and should be saved
     * by your application.
     * @var string
     */
    private $_access_token = null;

    /**
     * SmartFile Access Secret
     * This is given to you after you call getAccessToken() and should be saved
     * by your application.
     * @var string
     */
    private $_access_secret = null;

    /**
     * nonce char list
     * @var string
     */
    private $_nonce_chars
        = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // }}}

    // {{{ __construct()

    /**
     * Private constructor. Set the oauth token and secret once
     * when you instantiate this class.
     *
     * @param string $client_token   Client OAuth Token
     * @param string $client_secret  Client OAuth Secret
     * @param string $request_token  OAuth Access Token
     * @param string $request_secret OAuth Access Secret
     *
     * @return null
     */
    function __construct(
        $client_token=null,
        $client_secret=null,
        $request_token=null,
        $request_secret=null
    ) {
        $this->_client_token = $client_token;
        $this->_client_secret = $client_secret;
        $this->_request_token = $request_token;
        $this->_request_secret = $request_secret;
    }

    // }}}

    /**
     * Generate a random string of x chars
     *
     * @param int $length Length of resultant string
     *
     * @return string
     */
    private function _genNonce($length=32)
    {
        $result = '';
        $cLength = strlen($this->_nonce_chars);
        for ($i=0; $i < $length; $i++) {
            $rnum = rand(0, $cLength);
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
     * @param array  $extra_headers Extra Headers Such as Authentication Information
     *
     * @return array
     */
    public function doRequest($uri, $method, $data=null, $extra_headers=array())
    {
        // Add the OAuth authentication information to the request
        $auth = 'oauth_consumer_key="' . $this->_client_token . '",oauth_token="' .
            $this->_access_token .'",' . 'oauth_nonce="' . $this->_genNonce() .
            '",oauth_timestamp="' . time() . '",oauth_signature_method="PLAINTEXT",'.
            'oauth_version="1.0",oauth_signature="' . $this->_client_secret . '&' .
            $this->_access_secret . '"';
        $extra_headers = $extra_headers . 'Authorization: OAuth ' . $auth . "\r\n";

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
        if ($this->_client_token == null || $this->_client_secret == null) {
            throw new Service_SmartFile_APIException(
                'You must first set client token and client secret. ' .
                'Use "new Service_SmartFile_OAuthClient(token, secret)" first.'
            );
        }
        $uri = $this->oauth_base_url . '/oauth/request_token/';
        $data = array(
            'oauth_version' => '1.0',
            'oauth_nonce' => $this->_genNonce(),
            'oauth_timestamp' => time(),
            'oauth_consumer_key' => $this->_client_token,
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_signature' => $this->_client_secret . '&'
        );
        if ($callback) {
            //push callback to beginning of array
            $data = array_merge(array('callback_uri' => $callback), $data);
        }
        $result = parent::doRequest($uri, 'post', $data);
        $result = $this->getBody($result);
        if ($result == 'Could not verify OAuth request.') {
            throw new Service_SmartFile_APIException(
                'Could not verify OAuth request.'
            );
        }

        //convert returned string to array for easy access
        parse_str($result, $result);
        $this->_request_token = $result['oauth_token'];
        $this->_request_secret = $result['oauth_token_secret'];
        return $result;
    }

    /**
     * The second step of the OAuth workflow.
     * Send the user to the URL obtained. They will authorize the application to
     * access their account.
     * The user will be given a verifier if a callback is not specified.
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        if ($this->_request_token == null) {
            throw new Service_SmartFile_APIException(
                'You must obtain a request token to request ' .
                'and access token. Use get_request_token() first.'
            );
        }

        return $this->oauth_base_url . '/oauth/authorize/?oauth_token=' .
            urlencode($this->_request_token);
    }

    /**
     * The final step of the OAuth workflow. After this the client can make
     * API calls.
     *
     * @param string $verifier Verifier given to user or passed to your callback.
     *
     * @return array
     */
    public function getAccessToken($verifier='')
    {
        $uri = $this->oauth_base_url . '/oauth/access_token/';
        $data = array(
            'oauth_version' => '1.0',
            'oauth_nonce' => $this->_genNonce(),
            'oauth_timestamp' => time(),
            'oauth_verifier' => $verifier,
            'oauth_signature_method' => 'PLAINTEXT',
            'oauth_consumer_key' => $this->_client_token,
            'oauth_token' => $this->_request_token,
            'oauth_signature' => $this->_client_secret . '%26' .
                $this->_request_secret
        );
        $result = parent::doRequest($uri, 'post', $data);
        $result = $this->getBody($result);
        //convert returned string to array for easy access
        parse_str($result, $result);
        $this->_access_token = $result['oauth_token'];
        $this->_access_secret = $result['oauth_token_secret'];
        return $result;
    }

}

?>
