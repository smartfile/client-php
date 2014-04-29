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

require_once '../Services/SmartFile/BasicClient.php';

// {{{ constants

/**
 * These constants are needed to access the API.
 */
define("API_KEY", "api-key");
define("API_PWD", "api-pass");

// }}}


/**
 * This function makes the directory create API call. It uses the BasicClient
 * class to handle the transport. Additional API calls could be supported
 * simply by writing additional wrappers that create the $data array and
 * use BasicClient to do the grunt work.
 *
 * @return null
 */
function createDirectory()
{
    $data = array(
        'path' => '/testdir2'
    );
    $client = new Service_SmartFile_BasicClient(API_KEY, API_PWD);
    $response = $client->post('/path/oper/create/', $data);
    var_dump($response);
}

createDirectory();

?>