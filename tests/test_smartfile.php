<?php

require_once '../Services/SmartFile/BasicClient.php';

/**
 * Test class for Services_SmartFile_BasicClient upload, download, move, delete
 */
class BasicClientTest extends PHPUnit_Framework_TestCase
{


    // public function testOne()
    // {
    //     $this->assertTrue(true);
    // }

    public function testUpload()
    {
        include_once '../Services/SmartFile/BasicClient.php';
        $api = new Service_SmartFile_BasicClient('*******', '*******');

        $api->upload('myfile.txt');
        $thinger = $api->get('/path/info/myfile.txt');
        return $thinger;

        $sizefile = filesize('myfile.txt');
        // echo $sizefile;
        $this->assertEquals($thinger['size'], $sizefile);

    }

}
?>
