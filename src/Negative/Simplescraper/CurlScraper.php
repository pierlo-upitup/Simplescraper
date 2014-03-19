<?php namespace Negative\Simplescraper;

use Negative\Simplescraper\ScraperInterface;
use \Exception;

class CurlScraper implements ScraperInterface {
	/**
	 * The configuration array
	 * 
	 * @var array
	 */
	private $config;

	/**
	 * The plain-text cURL response (HTML)	
	 *
	 * @var string
	 */
	private $response;

	/**
	 * Create a CurlScraper instance.
	 *
	 * @var array $config
	 */
	public function __construct($config = array()) 
	{
		$this->config = $config;
	}

	/**
	 * Retrieve the URL.
	 * Assumes the $url is valid and the document well-formed.
	 *
	 * @implements ScraperInterface::fetch
	 * 
	 * @param  string  $url 
	 * @return string $response
	 */
	public function fetch($url)
	{	
		// Exec cURL and return response if any
		$this->doCurlExec($url);

        // if ( false === $this->response || empty($this->response) ) { 
        //	throw new Exception('Empty page or could not reach URL: '.$this->url); 
        // }
      	return $this->response;
	}

	/**
	 * Download the images to the local filesystem.
	 *
	 * @implements ScraperInterface::downloadImages
	 *
	 * @param array $images
	 * @return array $downloads
	 */
	public function downloadImages(array $images) {
		
		// Parse minimum size requirement
		$minimum = $this->config['minimum_size'];
		if (!empty($minimum)) $minimum = explode('x', $minimum);
		
		// Loop through imgs
		$downloads = array();

		foreach($images as $i => $img) {

			// Figure out where to store the image
			$dst = $this->config['download_dir'];

			// Create directory if it doesn't exist			
			if (!file_exists($dst)) { mkdir($dst, 0777, true); }

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
				continue;
			}

			// Check if the file meets minimum requirements
			if (!empty($minimum) && file_exists($dst)) {
				if ($size = @getimagesize($dst)) {
					if ($size[0] < $minimum[0] || $size[1] < $minimum[1]) {
						unlink($dst);
						continue;
					}
				} else {
					unlink($dst);
				}
			}
			
			// Update the path			
			$downloads[] = str_replace(public_path(), '', $dst);
			if ( count($downloads) >= $this->config['max_imgs'] ) break;			
		}
		return $downloads;
	}

	private function getUserAgent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27';
	}

	private function doCurlExec($url)
	{
		$curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());
        $this->response = curl_exec($curl);
        curl_close($curl);
	}

	
} 