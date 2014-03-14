<?php

return array(

	// If defined, download the scraped images to this directory
	// PLEASE NOTE: use a dedicated folder if you want to use download_ttl
	'download_dir' => public_path().'/uploads/simplescraper/',
	
	// Delete downloded images after (seconds)
	// set to zero to prevent automatic cleanup
	'download_ttl' => 120, 
	

	// The maximum number of images to download
	'max_imgs' => 4,
	
	// If there is an open graph image, still scrape additional ones?
	'always_scrape_imgs' => true,
	
	// Set a minimum size for the images that are shown.  This requires
	// a download_dir to be set.
	'minimum_size' => '300x200'
);