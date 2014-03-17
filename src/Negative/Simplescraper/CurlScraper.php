<?php namespace Negative\Simplescraper;

/**
 * A cURL based scraper.
 * 
 */
class CurlScraper implements ScraperInterface {

	// Configuration array
	private $config;

	// The URL to scrape
	private $url;

	// The array with the page's data
	private $page = array();

	// The plain-text body of the cURL response (HTML)	
	private $response; 


	public function __construct($config = array()) 
	{
		$this->config = $config;
	}
	
	/**
	 * Fetch the URL, parse OpenGrah / meta tags,
	 * scrape images and optionally download them.
	 * 
	 * @param  string  $url  The URL to scrape
	 * @return array   $page The array with data
	 */
	public function fetch($url)
	{	
		// Assumes the URL is valid and sanitized.
		$this->url = $url;

		$curl = curl_init($this->url);

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $this->response = curl_exec($curl);
        curl_close($curl);

        // Parse the body for OpenGraph / metas
        $this->parse();

		// Now scrape images (gather "src" value of img tags)
		$this->scrapeImages();

		// Actually download images?
        if ( array_key_exists('download_dir', $this->config) && !empty($this->page['images']) ) {	
			$this->downloadImages();
		}
		
		unset($this->page['HTML']);
        return $this->page;
	}



	/**
	 * Parses the fetched HTML and returns an array of key -> value pairs
	 * 
	 * @param  [type] $HTML [description]
	 * @return [type]       [description]
	 */
	public function parse() 
	{	

		$old_libxml_error = libxml_use_internal_errors(true);

		$doc = new \DOMDocument();

		try { 
			$doc->loadHTML($this->response);
		} catch (\ErrorException $e) { 
			throw new \Exception('Could not load HTML for: '.$this->url);
		}
		
		libxml_use_internal_errors($old_libxml_error);

		$tags = $doc->getElementsByTagName('meta');

		if (!$tags || $tags->length === 0) {
			throw new \Exception('Empty page or could not scrape URL: '.$this->url);
		}
		

		$nonOgDescription = null;
		
		foreach ($tags AS $tag) {
			if ($tag->hasAttribute('property') &&
			    strpos($tag->getAttribute('property'), 'og:') === 0) {
				$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
				$this->page[$key] = $tag->getAttribute('content');
			}
			
			//Added this if loop to retrieve description values from sites like the New York Times who have malformed it. 
			if ($tag ->hasAttribute('value') && $tag->hasAttribute('property') &&
			    strpos($tag->getAttribute('property'), 'og:') === 0) {
				$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
				$this->page[$key] = $tag->getAttribute('value');
			}
			//Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
			if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') {
                $nonOgDescription = $tag->getAttribute('content');
            }
			
		}
		//Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
		if (!isset($this->page['title'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $this->page['title'] = $titles->item(0)->textContent;
            }
        }
        if (!isset($this->page['description']) && $nonOgDescription) {
            $this->page['description'] = $nonOgDescription;
        }

		if (empty($this->page)) { throw new \Exception('Empty page or could not scrape URL: '.$this->url); }
		
		$this->page['HTML'] = $this->response;
	}

	/**
	 * Get all the image tags on a URL
	 */
	public function scrapeImages() {

		$html = $this->response;

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
			return $this->retrieveAbsoluteURL($img);
		}, $imgs);
		
		$this->page['images'] = $imgs;


	}

	/**
	 * Get a full URL to an image.  This is public so it can be executed by the
	 * anonymous function that is created in array_map() above.  In PHP 5.4
	 * Closure::bind() could be used.
	 * @param $img A reference to an image
	 */
	public function retrieveAbsoluteURL($img) {
	
		// Current URL protocol
		preg_match('#^(https?)://[^/]+#i', $this->url, $matches);
		$protocol = $matches[1];
		$protocol_and_domain = $matches[0];
		
		// Do nothing if image's URL already has protocol
		if (preg_match('#^http#i', $img)) return $img;
		
		// If a wildcard protocol (i.e. //domain.com/path/...), prepend current protocol
		if (preg_match('#^//#', $img)) return $protocol . ':' . $img;
		
		// If a relative path, append the full url
		if (preg_match('#^[^/]#', $img)) {
		
			// Get rid of anthing after the last slash.  Thus, the filenmae
			$this->url = preg_replace('#[^/]*$#', '', $this->url);
			
			// Foreach ../ in the relative url, delete a directory from the end of the url
			$img = str_replace('../', '', $img, $count);
			$this->url = preg_replace('#[^/]*/$#', '', $this->url, $count);

			// Get rid of any remaining ./s in the img url
			$img = str_replace('./', '', $img);
			
			// Append what remains of the img path onto what remains of the url
			return $this->url . $img;
		}
		
		// If an absolute path, append the protocol and domain
		if (preg_match('#^/#', $img)) return $protocol_and_domain . $img;
		
		// If it hasn't been catched yet, it's some new condition I haven't accounted for
		throw new \Exception('Unaccounted for ref: '.$img);
	}

	
	/**
	 * Download all the images to the local filesystem
	 */
	public function downloadImages() {
		
		// Parse minimum size requirement
		$minimum = $this->config['minimum_size'];
		if (!empty($minimum)) $minimum = explode('x', $minimum);
		
		// Loop through imgs
		$downloads = array();

		foreach($this->page['images'] as $i => $img) {

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
		$this->page['images'] = $downloads;

		return $this->page;
		
	}
} 