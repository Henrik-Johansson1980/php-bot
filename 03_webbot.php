<?php

/**
 * WebBot example
 */

// load WebBot library with bootstrap
require_once './lib/WebBot/bootstrap.php';

// URLs to fetch data from
$urls = [
    'index' => 'localhost:5000',
    'test' => 'localhost:5000/test.php',
];


// set WebBot object
$webbot = new \WebBot\WebBot($urls);

// execute fetch data from URLs
$webbot->execute();


// display each document
foreach ($webbot->getDocuments() as $document) {
    $data = $document->find('<title>', '</title>'); 

    if ($data) {
        if ($webbot->store(urlencode($document->url) . '.dat', $data)) {
            echo 'Data saved <br />';
        } else {
            echo 'Failed to save data: ' . $webbot->error . '<br />';
        }
    } else {
        echo 'Data not found <br />';
    }
}
