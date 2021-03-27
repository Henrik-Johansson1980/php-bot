<?php
/**
 * Example HTTP GET request
 */

// include our classes
require_once './lib/HTTP/bootstrap.php';

// execute example HTTP GET request
$response = \HTTP\Request::head('http://localhost:5000/');

// display response status
if($response->success)
{
    echo 'Successful request <br />';
}
else
{
    echo 'Error: request failed, status code: ' 
    . $response->getStatusCode() . '<br />'; // prints  status code
}

// print out HTTP response (\HTTP\Response object)
echo '<pre>' . print_r($response, true) . '</pre>';