<?php namespace Negative\Simplescraper;


class CurlScraper implements ScraperInterface {

	public static function fetch($URI)
	{
		$curl = curl_init($URI);

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $response = curl_exec($curl);

        curl_close($curl);

        return self::parse($response);        
	}

	/**
	 * Parses the fetched HTML and returns an array of key -> value pairs
	 * 
	 * @param  [type] $HTML [description]
	 * @return [type]       [description]
	 */
	public static function parse($HTML) 
	{	
		$old_libxml_error = libxml_use_internal_errors(true);

		$doc = new \DOMDocument();
		$doc->loadHTML($HTML);
		
		libxml_use_internal_errors($old_libxml_error);

		$tags = $doc->getElementsByTagName('meta');
		if (!$tags || $tags->length === 0) {
			return false;
		}

		$page = array();

		$nonOgDescription = null;
		
		foreach ($tags AS $tag) {
			if ($tag->hasAttribute('property') &&
			    strpos($tag->getAttribute('property'), 'og:') === 0) {
				$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
				$page[$key] = $tag->getAttribute('content');
			}
			
			//Added this if loop to retrieve description values from sites like the New York Times who have malformed it. 
			if ($tag ->hasAttribute('value') && $tag->hasAttribute('property') &&
			    strpos($tag->getAttribute('property'), 'og:') === 0) {
				$key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
				$page[$key] = $tag->getAttribute('value');
			}
			//Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
			if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') {
                $nonOgDescription = $tag->getAttribute('content');
            }
			
		}
		//Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
		if (!isset($page['title'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $page['title'] = $titles->item(0)->textContent;
            }
        }
        if (!isset($page['description']) && $nonOgDescription) {
            $page['description'] = $nonOgDescription;
        }

		if (empty($page)) { return false; }
		
		$page['HTML'] = $HTML;

		return $page;
	}
}