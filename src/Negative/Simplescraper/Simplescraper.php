<?php namespace Negative\Simplescraper;


use Negative\Simplescraper\CurlScraper;
use Negative\Simplescraper\RegexpParser;
use Negative\Simplescraper\Helpers\FilesystemHelper;
use Negative\Simplescraper\Helpers\InputHelper;

class Simplescraper {

	/**
	 * The configuration array.
	 * 
	 * @var array
	 */
	private $config;

	
	/**
	 * The URL to be scraped.
	 *
	 * @var string 
	 */
	private $url;

	/**
	 * The scraper instance. 
	 *
	 * @var Negative\Simplescraper\ScraperInterface
	 */
	private $scraper;

	/**
	 *	The HTML parser interface.
	 *
	 * @var Negative\Simplescraper\HtmlParserInterface
	 */
	private $parser;

	/**
	 * Configuration defaults.
	 *
	 * @var array 
	 */
	private $defaults = array(
		'download_dir' => '/var/www/uploads/simplescraper/', // Absolute path
		'download_ttl' => 120, 		
		'max_imgs' => 1,
		'minimum_size' => '300x200'
	);

	/**
	 * Create a Simplescraper instance.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) 
	{
		// Make sure $config is fine.
		if ( array_keys(array_intersect_key($config, $this->defaults)) !== array_keys($this->defaults)) {
			$this->config = $this->defaults;		
		} else {
			$this->config = $config;
		}
		// Instantiate classes
		$this->scraper = new CurlScraper($this->config);
		$this->parser = new DOMDocumentParser();
	}

	/**
	 * Collect data for a given URL. 
	 * The response is an array with 
	 * - title
	 * - description
	 * - images - An array of downloaded images or absolute URLs
	 */	
	public function lookup($url)
	{
		// Make sure the URL is good to work with
		$this->url = InputHelper::sanitizeURL($url);
		
		// Scrape the URL. Look for Open Graph tags 
		// or fallback to standard meta tags.
		$html = $this->scraper->fetch($this->url);

		$page = $this->parser->parse($html, $this->url);

        if (empty($page)) { 
        	throw new Exception('Empty page or could not scrape URL: '.$this->url); 
        }

		// Actually download images?
        if ( array_key_exists('download_dir', $this->config) && !empty($page['images']) ) {	
			// $page['images'] = $this->scraper->downloadImages($page['images']);
		}

		// Clean up old files?
		if ( array_key_exists('download_ttl', $this->config) && $this->config['download_ttl'] > 0) {
			FilesystemHelper::deleteFilesOlderThan( $this->config['download_ttl'], $this->config['download_dir']);
		}

		// Return findings
		return $page;
	}

	

}