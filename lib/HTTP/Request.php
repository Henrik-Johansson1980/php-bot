<?php

namespace HTTP;

/**
 *  HTTP Request class – execute HTTP get and head requests
 * 
 * @package HTTP
 */
class Request
{

  /**
   * Format timeout in seconds, if no timeout use default timeout
   *
   * @param int|float $timeout (seconds)
   * @return int|float
   */
  private static function formatTimeout(int $timeout = 0): float
  {
    // Format timeout value.
    $timeout = (float) $timeout;
    $timeout = ($timeout < 0.1) ? 60 : $timeout;
    return $timeout;
  }

  /**
   * Parse HTTP response
   *
   * @param string $body
   * @param array $header
   * @return \HTTP\Response
   */
  private static function parseResponse(string $body, array $header): \HTTP\Response
  {
    $statusCode = 0;
    $contentType = '';

    if (is_array($header && count($header) > 0)) {
      foreach ($header as $v) {

        if (substr($v, 0, 4) === 'HTTP' && strpos($v, ' ') !== false) {
          // Parse status code.
          $statusCode = (int) substr($v, strpos($v, ' '), 4);
        }
        // ex: Content-Type: *; charset=*
        else if (strncasecmp($v, 'Content-Type:', 13) === 0) {
          $content_type = $v;
        }
      }
    }
    return new \HTTP\Response($statusCode, $contentType, $body, $header);
  }
}
