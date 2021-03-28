<?php
namespace WebBot;

/**
 * Bootstrap file
 *
 * @package WebBot
 */

// load our HTTP package
require_once './lib/HTTP/bootstrap.php';

// load our WebBot package classes
require_once './lib/WebBot/Document.php';
require_once './lib/WebBot/WebBot.php';

// set unlimited execution time
set_time_limit(0);

// set default timeout to 30 seconds
\WebBot\WebBot::$confDefaultTimeout = 30;

// set delay between fetches to 1 seconds
\WebBot\WebBot::$confDelayBetweenFetches = 1;

// do not use HTTPS protocol (we'll use HTTP protocol)
\WebBot\WebBot::$confForceHttps = false;

// do not include document field raw values
\WebBot\WebBot::$confIncludeDocumentFieldRawValues = false;

// storage directory for storing data
\WebBot\WebBot::$confStoreDir = './data/';