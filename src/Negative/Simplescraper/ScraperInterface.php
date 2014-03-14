<?php namespace Negative\Simplescraper;


interface ScraperInterface {
	/**
	 * Fetch a URL and return an array of data
	 * @param  string $URL The URL to be fetched
	 * @return array The scraped data
	 */
	public static function fetch($URL);
}