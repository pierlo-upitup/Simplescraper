<?php namespace Negative\Simplescraper;

use Negative\Simplescraper\opengraph\OpenGraph as OpenGraph;


class Simplescraper {

	private $url;

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
		$this->url = $this->_sanitize_url($url);
		
		// Look for Open Graph tags as well as standard meta tags. This call
		// will get regular title and description metas.
		// $graph = OpenGraph::fetch($this->url);

		$data = $this->scraper->fetch($url);

		if ( empty($data) ) throw new \Exception('URL could not be scraped: '.$this->url);
		

		// Scrape images?
		if ( array_key_exists('always_scrape_imgs', $this->config) && 
			true === $this->config['always_scrape_imgs'] ) {
				$data['images'] = $this->_scrape_images($data['HTML']);
		}

		// Download images to the local filesystem
		if ( array_key_exists('download_dir', $this->config) && !empty($data['images']) ) {	
			$data['images'] = $this->_download_images($data['images']);
		}

		// Clean up old files?
		if ( array_key_exists('download_ttl', $this->config) && $this->config['download_ttl'] > 0) {
			$this->_cleanup();
		}

		unset($data['HTML']);
		
		// Return findings
		return $data;
	}
	
	/**
	 * Get all the image tags on a URL
	 */
	private function _scrape_images($html) {
		
		/* Collect all img tags:
		<\s*img       ---> "<" followed by zero or more spaces/tabs/newline (?) 
		[^>]          ---> not followed by an immediately closing tab (avoid empty elements such as "<img>")
		*             ---> also match "<imgsrc" (for some reason..?)
		(?:'|\")?     ---> "(?:"	Non-capturing group, single or double quotes once or more
		([^'\"\s]*)   ---> anything that does not start with a single quote, double or empty character (the filename)
		(?:'|\")?     ---> followed (or not) by a single or double quote
		[^>]*>        ---> and it ends at the first ">"
		*/
		preg_match_all("#<\s*img[^>]*src=(?:'|\")?([^'\"\s]*)(?:'|\")?[^>]*>#i", $html, $matches);
		if (empty($matches)) return array();
		$imgs = $matches[1];
		
		// Only support the most common image formats
		$imgs = array_filter($imgs, function($img) {
			return preg_match('#(jpg|jpeg|png)$#i', $img);
		});

		// Make sure there are no dupes
		$imgs = array_unique($imgs);
		
		// Prepend domain and url to all images
		$imgs = array_map(function($img) {
			return $this->parse_ref($img, $this->url);
		}, $imgs);
		
		// Return massaged set of images
		return $imgs;
	}
	
	/**
	 * Get a full URL to an image.  This is public so it can be executed by the
	 * anonymous function that is created in array_map() above.  In PHP 5.4
	 * Closure::bind() could be used.
	 * @param $img A reference to an image
	 * @param $url The URL that was being looked up
	 */
	public function parse_ref($img, $url) {
	
		// Current protocol
		preg_match('#^(https?)://[^/]+#i', $url, $matches);
		$protocol = $matches[1];
		$protocol_and_domain = $matches[0];
		
		// Do nothing if url has protocol
		if (preg_match('#^http#i', $img)) return $img;
		
		// If a wildcard protocol (i.e. //domain.com/path/...), match the current protocol
		if (preg_match('#^//#', $img)) return $protocol . ':' . $img;
		
		// If a relative path, append the full url
		if (preg_match('#^[^/]#', $img)) {
		
			// Get rid of anthing after the last slash.  Thus, the filenmae
			$url = preg_replace('#[^/]*$#', '', $url);
			
			// Foreach ../ in the relative url, delete a directory from the end of the url
			$img = str_replace('../', '', $img, $count);
			$url = preg_replace('#[^/]*/$#', '', $url, $count);

			// Get rid of any remaining ./s in the img url
			$img = str_replace('./', '', $img);
			
			// Append what remains of the img path onto what remains of the url
			return $url . $img;
		}
		
		// If an absolute path, append the protocol and domain
		if (preg_match('#^/#', $img)) return $protocol_and_domain . $img;
		
		// If it hasn't been catched yet, it's some new condition I haven't accounted for
		throw new Exception('Unaccounted for ref: '.$img);
		
	}
	
	/**
	 * Download all the images to the local filesystem
	 */
	private function _download_images($imgs) {
		
		// Parse minimum size requirement
		$minimum = $this->config['minimum_size'];
		if (!empty($minimum)) $minimum = explode('x', $minimum);
		
		// Loop through imgs
		$downloads = array();
		foreach($imgs as $i => $img) {

			// Figure out where to store the image
			$dst = $this->config['download_dir'];

			if ( ! \File::exists($dst)) \File::makeDirectory($dst);

			$file = uniqid().'.'.strtolower(pathinfo(parse_url($img, PHP_URL_PATH), PATHINFO_EXTENSION));
			$dst .= $file;
			
			// Download the image
			$fp = fopen($dst, 'w');
			$ch = curl_init($img);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$success = curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			

			// Check if download failed
			if (! $success ) {
				// throw new Exception('File download error: '.$img.' -> '.$dst);
				continue;
			}

			// Check if the file meets minimum requirements
			if (!empty($minimum) && file_exists($dst)) {
				if ($size = @getimagesize($dst)) {
					if ($size[0] < $minimum[0] || $size[1] < $minimum[1]) {
						unlink($dst);
						continue;
					}
				} else unlink($dst);
			}
			
			// Update the path			
			$downloads[] = str_replace(public_path(), '', $dst);
			if ( count($downloads) >= $this->config['max_imgs'] ) break;			
		}
		
		// Return the images
		return $downloads;
		
	}

	/**
	 * Delete files older than one hour from the simplescraper upload folder.
	 * 
	 * @return [type] [description]
	 */
	private function _cleanup()
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
	private function _sanitize_url($url) 
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