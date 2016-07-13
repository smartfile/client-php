.. image:: https://d2xtrvzo9unrru.cloudfront.net/brands/smartfile/logo.png
   :alt: SmartFile

A `SmartFile`_ Open Source project. `Read more`_ about how SmartFile
uses and contributes to Open Source software.


Summary
------------

This library includes two API clients. Each one represents one of the supported
authentication methods. ``BasicClient`` is used for HTTP Basic authentication,
using an API key and password. ``OAuthClient`` is used for OAuth (version 1) authentication,
using tokens, which will require user interaction to complete authentication with the API.

Both clients provide a thin wrapper around an HTTP library, taking care of some
of the mundane details for you. The intended use of this library is to refer to
the API documentation to discover the API endpoint you wish to call, then use
the client library to invoke this call.

SmartFile REST API information is available at the
`SmartFile developer site <https://app.smartfile.com/api/>`_.

Installation
------------

via source code / GitHub.

::

    $ git clone https://github.com/smartfile/client-php.git smartfile

More information is available at `GitHub <https://github.com/smartfile/client-php>`_

Usage
-----

Choose between Basic and OAuth authentication methods, then continue to use the SmartFile API.

Some of the details this library takes care of are:

* Encoding and decoding of parameters and return values. You deal with PHP
  types only.
* URLs, using the API version, endpoint, and object ID, the URL is created for
  you.
* Authentication. Provide your API credentials to this library, it will take
  care of the details.

Basic Authentication
--------------------

sample PHP code::

       include_once 'Services/SmartFile/BasicClient.php';
       $api = new Service_SmartFile_BasicClient('**********', '**********');
       echo $api->doRequest('/ping/', 'get');


OAuth Authentication
--------------------

This section is currently under construction and may not work as expected.

Authentication using OAuth authentication is bit more complicated, as it involves tokens and secrets.

sample PHP code::

    include_once 'Services/SmartFile/OAuthClient.php';
    $api = new Service_SmartFile_OAuthClient('**********', '**********');
    // Be sure to only call each method once for each OAuth login

    // If you want to change the app url to your custom domain.
    //$api->api_base_url= 'https://mysite.smartfile.com/api/2';
    //$api->oauth_base_url = str_replace('/api/2', '', $api->api_base_url);

    // This is the first step with the client, which should be left alone
    $api->getRequestToken();

    // Redirect users to the following URL:
    echo "In your browser, go to: " . $api->getAuthorizationUrl() . "\n";
    // This example uses raw_input to get the verification from the console:
    $clientVerification = trim(fgets(STDIN));
    $api->getAccessToken($clientVerification);
    //$api->get('/ping');

Calling endpoints
-----------------

Once you instantiate a client, you can use the get/put/post/delete methods
to make the corresponding HTTP requests to the API. There is also a shortcut
for using the GET method, which is to simply invoke the client.



Some endpoints accept an ID, this might be a numeric value, a path, or name,
depending on the object type. For example, a user's id is their unique
``username``. For a file path, the id is it's full path.


File transfers
--------------

Uploading and downloading files is supported.

To upload a file::

    include_once 'Services/SmartFile/BasicClient.php';
    $api = new Service_SmartFile_BasicClient('**********', '**********');

    $api->upload('file.txt');

Downloading is automatic, if the ``'Content-Type'`` header indicates
content other than the expected JSON return value, then a file-like object is
returned.


To download a file::

    include_once 'Services/SmartFile/BasicClient.php';
    $api = new Service_SmartFile_BasicClient('**********', '**********');

    $api->download('myfile.txt');


Tasks
-----

Operations are long-running jobs that are not executed within the time frame
of an API call. For such operations, a task is created, and the API can be used
to poll the status of the task.

To delete a file::

    include_once 'Services/SmartFile/BasicClient.php';
    $api = new Service_SmartFile_BasicClient('**********', '**********');

    $api->delete("file.txt");


To move a file::

    include_once 'Services/SmartFile/BasicClient.php';
    $api = new Service_SmartFile_BasicClient('**********', '**********');

    $api->move('file.txt', '/myFolder');

Running Tests
-----

To run the test_smartfile.php test, from within the tests folder run::

    API_KEY="**********" API_PASS="**********" phpunit test_smartfile.php

.. _SmartFile: http://www.smartfile.com/
.. _Read more: http://www.smartfile.com/open-source.html
