<?php

require_once '../Services/SmartFile/BasicClient.php';
include_once '../Services/SmartFile/BasicClient.php';

$API_KEY = getenv('API_KEY');
$API_PASS = getenv('API_PASS');

if ($API_KEY == NULL || $API_PASS == NULL)
{
    throw new Service_SmartFile_RequestException('Must have API key and password.');
}

$api = new Service_SmartFile_BasicClient($API_KEY, $API_PASS);

/**
 * Test class for Services_SmartFile_BasicClient upload, download, move, delete
 */
class BasicClientTest extends PHPUnit_Framework_TestCase
{

    public function testUpload()
    {
        GLOBAL $api;

        // creates the file to test
        $myfile = fopen('myfile.txt', 'w');
        $txt = 'this is a test file';
        fwrite($myfile, $txt);
        fclose($myfile);

        $api->upload('myfile.txt');
        $file_info = $api->get('/path/info/myfile.txt');
        return $file_info;

        $sizefile = filesize('myfile.txt');
        $this->assertEquals($file_info['size'], $sizefile);
    }

    public function testDownload()
    {
        GLOBAL $api;

        $myfile = fopen('myfile.txt', 'rb');

        $api->download('myfile.txt');
        $f = fopen('myfile.txt', 'rb');
        $this->assertEquals(fgets($f), fgets($myfile));
    }

    public function testMove()
    {
        GLOBAL $api;

        $api->move('myfile.txt', '/newFolder');

        $file_info_original = $api->get('/path/info/myfile.txt');
        return $file_info_original;

        $file_info_moved = $api->get('/path/info/newFolder/myfile.txt');
        return $file_info_moved;

        $this->assertFalse($file_info_original['url'], $file_info_moved['url']);
    }

    public function testDelete()
    {
        GLOBAL $api;

        $api->remove('/newFolder/myfile.txt/');

        $file_info_original = $api->get('/path/info/newFolder/myfile.txt');
        return $file_info_original;

        $file_info = $api->get('/path/info/');
        return $file_info;

        $this->assertFalse($file_info_original['url'], $file_info['url']);
    }
}
?>
