<?php namespace Negative\Simplescraper\Helpers;

use \Exception;

class InputHelper {

	/**
	 * Returns a RFC2396 compliant URL.
	 * 
	 * Prepends the HTTP scheme if not present.
	 * 
	 * @param  string $url The URL to sanitize
	 * @return mixed $url The sanitized URL or FALSE on failure
	 */
	public static function sanitizeUrl($url)
	{
		// Sanitize input first
		$url = filter_var(trim(urldecode($url)), FILTER_SANITIZE_URL);
		
		// parse_url returns false on "seriously malformed URLs"
		if ($parsed = parse_url($url)) {
			// Make sure trailing scheme is present		
			if (empty($parsed['scheme'])) {		
				$url = 'http://'.$url;
			}
			// RFC2396 compliancy test
			if(filter_var($url, FILTER_VALIDATE_URL)) { 
				return $url;
			}
		}
		return false;
	}
}