<?php

namespace WebBot;

use HTTP\Request;
use WebBot\Document;

/**
 * WebBot class - fetch document data from Website URLs
 *
 * @package WebBot
 */
class WebBot
{
	/**
	 * Documents
	 *
	 * @var array (of \WebBot\Document)
	 */
	private $documents = [];

	/**
	 * Fetch URLs
	 *
	 * @var array
	 */
	private $urls = [];

	/**
	 * Trace log
	 *    
	 * @var array
	 */
	private $log = [];

	/**
	 * Directory for storing data
	 *
	 * @var string
	 */
	public static $confStoreDir;

	/**
	 * Store data to storage directory file
	 *
	 * @param string $filename
	 * @param string $data
	 * @return boolean
	 */
	public function store($filename, $data)
	{
		// check if data directory exists
		if (!is_dir(self::$confStoreDir)) {
			$this->error = 'Invalid data storage directory "' . self::$confStoreDir . '"';
			return false;
		}

		// check if data directory is writable
		if (!is_writable(self::$confStoreDir)) {
			$this->error = 'Data storage directory "' . self::$confStoreDir . '" is not writable';

			return false;
		}

		// format data directory and filename
		$filePath = self::$confStoreDir . rtrim($filename, DIRECTORY_SEPARATOR);

		// flush existing data file
		if (is_file($filePath)) {
			unlink($filePath);
		}

		// store data in data file
		if (file_put_contents($filePath, $data) === false) {
			$this->error = 'Failed to save data to data file "' . $filePath . '"';
			return false;
		}

		return true;
	}

	/**
	 * Add message to log trace
	 *
	 * @param string $message
	 * @param string $method
	 * @return void
	 */
	private function log($message, $method)
	{
		$this->log[] = "$message ($method) ";
	}

	/**
	 * Trace log getter
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Default timeout configuration setting (seconds)
	 *
	 * @var int|float
	 */
	public static $confDefaultTimeout = 30;

	/**
	 * Delay between fetches (seconds), 0 (zero) for no delay
	 *
	 * @var int|float
	 */
	public static $confDelayBetweenFetches = 0;

	/**
	 * Force HTTPS protocol when fetching URL data
	 *
	 * Note: will not override URL protocol if set, ex: fetch URL 'http://url' will
	 * not be forced to 'https://url', only 'url' gets forced to 'https://url'
	 *
	 * @var boolean
	 */
	public static $confForceHttps = false;

	/**
	 * Include document field raw values when matching field patterns
	 * ex: '<h2>(.*)</h2>' => [(field value)'heading', (field raw value)'<h2>heading</h2>']
	 *
	 * @var boolean
	 */
	public static $confIncludeDocumentFieldRawValues = false;

	/**
	 * Error message (false when no errors)
	 *
	 * @var boolean|string
	 */
	public $error = false;

	/**
	 * Successful fetch flag
	 *
	 * @var boolean
	 */
	public $success = false;

	/**
	 * Document count (distinct documents)
	 *
	 * @var int
	 */
	public $totalDocuments = 0;

	/**
	 * Document count of failed fetched documents
	 *
	 * @var int
	 */
	public $totalDocumentsFailed = 0;

	/**
	 * Document count of successfully fetched documents
	 *
	 * @var int
	 */
	public $totalDocumentsSuccess = 0;

	/**
	 * Init
	 *
	 * @param array $urls
	 * @param array $documentFields
	 *		(fields with patterns, ex: ['title' => '<title.*?>(.*)</title>', [...]])
	 */
	public function __construct(array $urls)
	{
		$this->urls = $urls;

		if (count($this->urls) < 1) // ensure URLs are set
		{
			$this->error = 'Invalid number of URLs (zero URLs)';
			$this->log($this->error, __METHOD__);
		} else {
			$this->log(count($this->urls) . ' URL(s) initialized', __METHOD__);
		}
	}

	/**
	 * Format URL for fetch, ex: 'www.[dom].com/page' => 'http://www.[dom].com/page'
	 *
	 * @param string $url
	 * @return string
	 */
	private function formatURL($url)
	{
		$url = trim($url);

		// do not force protocol if protocol is already set
		if (!preg_match('/^https?\:\/\/.*/i', $url)) // match 'http(s?)://*'
		{
			// set protocol
			$url = (self::$confForceHttps ? 'https' : 'http') . '://' . $url;
		}

		return $url;
	}

	/**
	 * Fetch documents from fetch URLs
	 *
	 * @return void
	 */
	public function execute()
	{
		$i = 0;

		$this->log('Executing bot URL fetches', __METHOD__);

		foreach ($this->urls as $id => $url) {
			if ($i > 0 && (float)self::$confDelayBetweenFetches > 0) // fetch delay
			{
				sleep((float)self::$confDelayBetweenFetches);
			}

			if (!empty($url)) {
				$md5 = md5($url);

				if (!isset($this->documents[$md5])) // distinct documents only
				{
					$this->totalDocuments++; // add to document distinct count

					$this->documents[$md5] = new Document(
						Request::get(
							$this->formatURL($url),
							self::$confDefaultTimeout
						),
						$id
					);

					// set fetched counts
					if ($this->documents[$md5]->success) {
						$this->totalDocumentsSuccess++;
					} else {
						$this->totalDocumentsFailed++;
					}
				}
			} else {
				$this->error = 'Invalid URL detected (empty URL with ID "' . $id . '")';
				$this->log($this->error, __METHOD__);
			}

			$i++;
		}

		$this->log($this->totalDocuments . ' total documents', __METHOD__);
		$this->log($this->totalDocumentsSuccess . ' documents fetched successfully', __METHOD__);
		$this->log($this->totalDocumentsFailed . ' documents failed to fetch', __METHOD__);

		// set success if no errors
		$this->success = !$this->error;
	}

	/**
	 * Documents getter
	 *
	 * @return array (of \WebBot\Document)
	 */
	public function getDocuments()
	{
		return $this->documents;
	}
}
