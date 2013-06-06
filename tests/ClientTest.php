<?php

require_once '../Services/SmartFile/Client.php';

/**
 * Test class for Services_SmartFile_Client
 */
class ClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the ping endpoint. Ping is the only endpoint that can be tested
     * without authentication.
     *
     * @return null
     */
    public function testPing()
    {
        $test = new Service_SmartFile_Client();
        $response = $test->get('/ping/', null);
        $this->assertTrue($response['ping']=='pong');
    }

}
?>