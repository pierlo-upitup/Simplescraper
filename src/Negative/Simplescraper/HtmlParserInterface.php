<?php namespace Negative\Simplescraper;


interface HtmlParserInterface {

	/**
	 * Parse an HTML blob and return title, description and images.
	 * Check for OpenGraph data first.
	 *
	 * @param $html The HTML blob to parse
	 * @param $url  The URL for absolute image paths
	 * 
	 * @return array The page data
	 */
	public function parse($html, $url);
}