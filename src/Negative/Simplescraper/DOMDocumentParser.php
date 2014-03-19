<?php namespace Negative\Simplescraper;

use Negative\Simplescraper\HtmlParserInterface;
use \DOMDocument;
use \ErrorException;
use \Exception;

class DOMDocumentParser implements HtmlParserInterface {

	/**
	 * The parsed data extracted from the HTML blob.
	 * 
	 * @var array
	 */
	private $page = [];

	/**
	 * Uses the DOMDocument library to parse the HTML 
	 * and extract the title, description, and images.
	 * 
	 * @param  string 	$html 	The HTML to be parsed
	 * @param  string 	$url 	A valid URL for the absolute paths
	 * @return mixed 	The parsed data array or false
	 */
	public function parse($html, $url) 
	{
		// Assign $url to the page array
		$this->page = ['url' => $url];

		// Catch errors when loading an ill-formed html string
		$old_libxml_error = libxml_use_internal_errors(true);
		$doc = new DOMDocument();
		try { 
			$doc->loadHTML($html);
		} catch (ErrorException $e) {
			return false;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($old_libxml_error);

		// Basic <title>
		$titles = $doc->getElementsByTagName('title');
		if ($titles->length > 0) {
			$this->page['title'] = $titles->item(0)->textContent;
		}
		
		// OpenGraph	
		$metaTags = $doc->getElementsByTagName('meta');
		$ogTags = [];	
		$nonOgDescription = null;

		// This will fetch all opengraph tags too
		foreach ($metaTags AS $tag) {
			if ($tag->hasAttribute('property') &&
			    strpos($tag->getAttribute('property'), 'og:') === 0) {
				$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
				$this->page[$key] = $tag->getAttribute('content');
			}
			
			// Added this if loop to retrieve description values from sites like the New York Times who have malformed it. 
			if ($tag ->hasAttribute('value') && $tag->hasAttribute('property') &&
			    strpos($tag->getAttribute('property'), 'og:') === 0) {
				$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
				$this->page[$key] = $tag->getAttribute('value');
			}
			// Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
			if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') {
                $nonOgDescription = $tag->getAttribute('content');
            }
		}

        if (!isset($this->page['description']) && $nonOgDescription) {
            $this->page['description'] = $nonOgDescription;
        }

        if (!isset($this->page['title'])) {
            $this->page['title'] = $this->page['url'];
        }
		
		// Now scrape images
		$images = [];
		$xml = simplexml_import_dom($doc); // simpler xpath navigation
		$imgTags = $xml->xpath('//img');

		// Only support the most common image formats.
		foreach($imgTags as $img) {
			$src = (string)$img['src'];
			if ( ! preg_match('#(jpg|jpeg|png)$#i', $src)) continue;
			$images[] = $this->retrieveAbsoluteURL($src, $url);
		}

		// Remove duplicates.
		$this->page['images'] = array_unique($images);
		return $this->page;
	}

	

	/**
	 * Get a full URL to an image. 
	 * 
	 * @param $img A reference to an image
	 */
	private function retrieveAbsoluteURL($img, $url = '') {
		
		// Do nothing if img is absolute or no URL was provided.
		if (preg_match('#^http#i', $img) || empty($url)) return $img;

		// Check if supplied URL is valid anyway.
		if ( ! filter_var($url, FILTER_VALIDATE_URL)) return $img;
		
		// Append forward slash if not in the URL already
		if ( '/' !== substr($url, -1)) $url.='/';

		// Store current protocol.
		preg_match('#^(https?)://[^/]+#i', $url, $matches);
		$protocol = $matches[1];
		$protocol_and_domain = $matches[0];

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
	}

	
} 