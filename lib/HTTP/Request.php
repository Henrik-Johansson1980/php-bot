<?php
namespace HTTP;

/**
 * HTTP Request class – execute HTTP get, post and head requests
 *
 * @package HTTP
 */
class Request
{
	/**
	 * User agent
	 *
	 * @var string
	 */
	public static $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36';

	/**
	 * Format timeout in seconds, if no timeout use default timeout
	 *
	 * @param int|float $timeout (seconds)
	 * @return int|float
	 */
	private static function formatTimeout($timeout = 0)
	{
		$timeout = (float)$timeout; // format timeout value

		if($timeout < 0.1)
		{
			$timeout = 60; // default timeout
		}

		return $timeout;
	}

	/**
	 * Parse HTTP response
	 *
	 * @param string $body
	 * @param array $header
	 * @param string $url
	 * @return \HTTP\Response
	 */
	private static function parseResponse($body, $header, $url)
	{
		$statusCode = 0;
		$contentType = '';

		if(is_array($header) && count($header) > 0)
		{
			foreach($header as $v)
			{
				// ex: HTTP/1.x XYZ Message
				if(substr($v, 0, 4) == 'HTTP'
					&& strpos($v, ' ') !== false)
				{
					$statusCode = (int)substr($v,
						strpos($v, ' '), 4); // parse status code
				}
				// ex: Content-Type: *; charset=*
				else if(strncasecmp($v, 'Content-Type:', 13) === 0)
				{
					$contentType = $v;
				}
			}
		}

		return new \HTTP\Response($statusCode, $contentType, $body, $header, $url);
	}

	/**
	 * Execute HTTP GET request
	 *
	 * @param string $url
	 * @param int|float $timeout (seconds)
	 * @return \HTTP\Response
	 */
	public static function get($url, $timeout = 0)
	{
		$context = stream_context_create();

		stream_context_set_option($context, [
			'http' => [
				'timeout' => self::formatTimeout($timeout),
				// 'proxy' => 'tcp://0.0.0.0:8080', // proxy IP
				'header' => "User-Agent: " . self::$userAgent . "\r\n"
			]
		]);

		$http_response_header = null; // allow updating

		$responseBody = file_get_contents($url, false, $context);

		return self::parseResponse($responseBody, $http_response_header, $url);
	}

	/**
	 * Execute HTTP HEAD request
	 *
	 * @param string $url
	 * @param int|float $timeout (seconds)
	 * @return \HTTP\Response
	 */
	public static function head($url, $timeout = 0)
	{
		$context = stream_context_create();

		stream_context_set_option($context, [
			'http' => [
				'method' => 'HEAD',
				'timeout' => self::formatTimeout($timeout),
				// 'proxy' => 'tcp://0.0.0.0:8080', // proxy IP
				'header' => "User-Agent: " . self::$userAgent . "\r\n"
			]
		]);

		$http_response_header = null; // allow updating

		$responseBody =	file_get_contents($url, false, $context);

		return self::parseResponse($responseBody, $http_response_header, $url);
	}
}