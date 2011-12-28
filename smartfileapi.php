#!/usr/bin/php
<?
# These constants are needed to access the API.
define("API_URL", "http://app.smartfile.com/api/1");
define("API_KEY", "api-key");
define("API_PWD", "api-password");

# This function performs an HTTP request and parses the response.
# It raises an exception if the server cannot be contacted or if
# the server returns a status other than 201. Any status other
# that 201 results in the exception receiving the HTTP status in
# addition to the returned error message.
function http_request($uri, $data=null, $method) {
    $url = API_URL . $uri;
    $url_parts = parse_url($url);
    $host_header = $url_parts['host'];
    if (array_key_exists('port', $url_parts)) {
        $port = $url_parts['port'];
        $host_header .= ":" . $port;
    } else
        $port = 80;
    $data = http_build_query($data);
    $auth = base64_encode(API_KEY . ":" . API_PWD);
    # We use fsockopen to perform our HTTP request as that is the
    # most compatible way of doing so. This method should work on
    # just about any PHP installation and does not require cURL
    # or any other HTTP extensions.
    $fp = @fsockopen($url_parts['host'], $port, $errno, $errstr, 30); 
    if (!$fp)
        throw new Exception("Error contacting Server: " . $errstr);
    fputs($fp,  $method . " " . $url_parts['path'] . " HTTP/1.1\r\n" .
                "Host: " . $host_header . "\r\n" .
                "User-Agent: PHP SmartFile API Sample Client\r\n" .
                "Authorization: Basic " . $auth . "\r\n" .
                "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Content-Length: " . strlen($data) . "\r\n" .
                "\r\n");
    if ($method == 'POST')
        fputs($fp, $data);
    $response = '';
    while ($line = fread($fp, 4096)) {
        $response .= $line;
    }
    fclose($fp);

    # strip the HTTP headers:
    $sep = strpos($response, "\r\n");
    $http_status = substr($response, 0, $sep);
    list($ignored, $http_status, $ignored) = split(" ", $http_status);
    if ($http_status != '201') {
        # Non-201 status, so parse out the message and inform the caller
        # via an Exception.
        $sep = strpos($response, "\r\n\r\n");
        $response = substr($response, $sep + 4);
        $response = json_decode($response);
        throw new Exception($response->{'message'}, $http_status);
    }
}

# This function makes the User add API call. It uses the http_request
# function to handle the transport. Additional API calls could be supported
# simply by writing additional wrappers that create the $data array and
# use http_request to do the grunt work.
function create_user($fullname, $username, $password, $email) {
    $data = array(
        "name"          => $fullname,
        "username"      => $username,
        "password"      => $password,
        "email"         => $email,
    );
    http_request('/users/add/', $data, 'POST');
}

# A short function to ask the user a question and return their
# response.
function prompt($prompt='') {
    echo $prompt;
    return trim(fgets(STDIN));
}

function main() {
    # Ask the user for the required parameters. These will be
    # passed to the API via an HTTP POST request.
    $fullname = prompt('Please enter a full name: ');
    $username = prompt('Please enter a username: ');
    $password = prompt('Please enter a password: ');
    $email = prompt('Please enter an email address: ');
    try {
        # Try to create the new user...
        create_user($fullname, $username, $password, $email);
        print("Successfully created user " . $username . ".\n");
    }
    catch (Exception $e) {
        # Print the error message from the server on failure.
        print("Error creating user " . $username . ": " . $e->getMessage() . ".\n");
    }
}

# Start things off in main()
main();
?>