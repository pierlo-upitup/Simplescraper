<?php namespace Negative\Simplescraper;


class Simplescraper {

	/**
	* The configuration array
	*/
	private $config;


	public function __construct(ScraperInterface $scraper, array $config = array()) 
	{
		$this->scraper = $scraper; 
		$this->config = $config;		
	}

	/**
	 * Get info about a URL.  The response is an Response object that has:
	 * - title
	 * - description
	 * - images - An array of relative paths or URLs (if download option is off)
	 * 
	 * If no data can be found, false is returned.
	 */	
	public function lookup($url)
	{
		
		// Look for Open Graph tags as well as standard meta tags.
		$data = $this->scraper->fetch( $this->sanitizeURL($url) );

		// Clean up old files?
		if ( array_key_exists('download_ttl', $this->config) && $this->config['download_ttl'] > 0) {
			$this->deleteOldFiles();
		}
	
		// Return findings
		return $data;
	}
	
	
	
	

	/**
	 * Delete files older than one hour from the simplescraper upload folder.
	 * 
	 * @return [type] [description]
	 */
	private function deleteOldFiles()
	{
		$files = glob( $this->config['download_dir']."*");
	    foreach($files as $file) {
	        if(is_file($file)
	        && time() - filemtime($file) >= $this->config['download_ttl']) { 
	            unlink($file);
	        }
	    }
	}

	/**
	 * Make sure the URL is "clean" and valid for further processing.
	 * 
	 * @param  string $url 
	 * @return string $url the sanitized url
	 */
	private function sanitizeURL($url) 
	{
		$url = trim(urldecode($url));
		
		if  ( $ret = parse_url($url) ) { 
			if ( !isset($ret["scheme"]) ) {
				$url = "http://{$url}";
			}
		}

		// Require the URL to include the protocol
		if( ! filter_var($url, FILTER_VALIDATE_URL) ) throw new Exception('Invalid URL: '.$url);

		return $url;
	}

}