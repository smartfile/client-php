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

/**
 * SmartFile API base exception
 *
 * @category  Web_Services
 * @package   SmartFile_Client
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @link      http://pear.php.net/package/SmartFile
 */
class Service_SmartFile_APIException extends Exception
{
}

/**
 * Exception for issues regarding a request.
 *
 * @category  Web_Services
 * @package   SmartFile_Client
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @link      http://pear.php.net/package/SmartFile
 */
class Service_SmartFile_RequestException extends Service_SmartFile_APIException
{
}

/**
 * Exception for issues regarding a response.
 *
 * @category  Web_Services
 * @package   SmartFile_Client
 * @author    Ryan Johnston <github@shopandlearn.net>
 * @copyright 2012 SmartFile
 * @license   See LICENSE file
 * @link      http://pear.php.net/package/SmartFile
 */
class Service_SmartFile_ResponseException extends Service_SmartFile_APIException
{

    /** Parse response to add more info about the exception
     *
     * @param string $http_status HTTP Status code
     * @param string $response    HTTP Response
     *
     * @return null
     */
    public function __construct($http_status = 0, $response = '')
    {
        $catchelse = true;
        try
        {
            $json = json_decode($response, true);
        }
        catch (Exception $e)
        {
            $catchelse = false;
            if ($http_status == 404) {
                $message ='Invalid URL, check your API path';
            } else {
                $message = 'Server error; check response for errors';
            }
        }
        if ($catchelse) {
            //try...catch...else
            if (is_array($json) && array_key_exists('field_errors', $json)) {
                if (is_array($json['field_errors'])) {
                    $message = json_encode($json);
                } else {
                    $message = $json['field_errors'];
                }
            } else {
                $message = $json['detail'];
            }
        }
        if (!$message) {
            $message = $response;
        }
        parent::__construct($message);
    }
}
