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
        $api = new Service_SmartFile_BasicClient('*********', '**********');

        $api->upload('myfile.txt');
        $file_info = $api->get('/path/info/myfile.txt');
        return $file_info;

        $sizefile = filesize('myfile.txt');
        $this->assertEquals($file_info['size'], $sizefile);

    }

    public function testDownload()
    {
        include_once '../Services/SmartFile/BasicClient.php';
        $api = new Service_SmartFile_BasicClient('*********', '**********');

        $myfile = fopen('myfile.txt', 'rb');

        $api->download('myfile.txt');
        $f = fopen('myfile.txt', 'rb');
        $this->assertEquals(fgets($f), fgets($myfile));
    }

}
?>
