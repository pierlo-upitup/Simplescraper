<?php namespace Negative\Simplescraper;

interface ScraperInterface {

	/**
	 * Fetches a URL and returns an array with
	 * title, description, images (absolute paths).
	 * 
	 * @param  string $URL
	 * @return string $response The HTML response
	 */
	public function fetch($URL);

	/**
	 * Download images to a local folder.
	 * 
	 * @param  array $images Images to download
	 * @return array $downloads The path to the downloaded images
	 */
	public function downloadImages(array $images);
}