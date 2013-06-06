<?php

require_once '../Services/SmartFile/OAuthClient.php';

/**
 * Test class for Services_SmartFile_OAuthClient
 */
class OAuthClientTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test the get request token endpoint.
     *
     * @return null
     */
    public function testTokenRequest()
    {
        $test = new Service_SmartFile_OAuthClient('todo', 'todo');
        $result = $test->getRequestToken();
        $this->assertTrue(true); // @TODO
    }

    /**
     * Test the get request token endpoint with bad token/secret.
     *
     * @return null
     */
    public function testBadTokenRequest()
    {
        $test = new Service_SmartFile_OAuthClient('bad_data', 'bad_secret');
        $result = $test->getRequestToken();
        $this->assertTrue($result == 'Could not verify OAuth request.');
    }


}
?>