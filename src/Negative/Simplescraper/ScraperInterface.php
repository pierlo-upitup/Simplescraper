<?php namespace Negative\Simplescraper;


interface ScraperInterface {

	/**
	 * Fetches a URL and returns an array with
	 * title, description, images (absolute or relative path)
	 * 
	 * @param  string $URL The URL to be fetched
	 * @return array The scraped data 
	 */
	public function fetch($URL);
}