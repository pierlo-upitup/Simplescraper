## Simplescraper
[![Build Status](https://travis-ci.org/pierlo-upitup/Simplescraper.svg?branch=master)](https://travis-ci.org/pierlo-upitup/Simplescraper)

A (very) simple URL scraper that fetches title, description and images.

## Installation

Install via Composer

		composer require negative\simplescraper 

version: dev-master.

Add

		'Negative\Simplescraper\SimplescraperServiceProvider'

to your app/config.php $providers array


Please check vendor/negative/simplescraper/src/config/config.php for configuration options.

Publish the configuration file by running

    php artisan config:publish negative/simplescraper

and edit the configuration file under

    /app/config/packages/negative/simplescraper/config.php

That's about it.

## Usage

Simply call:

		Simplescraper::lookup('http://www.ikea.com/us/en/catalog/products/60202199/');

It will return an array like this:

		array(
			"url" => "http://www.ikea.com/us/en/catalog/products/60202199/",
			"title" => "FALSTER Table - gray  - IKEA",
			"description" => "IKEA - FALSTER, Table, gray , Polystyrene slats are weather-resistant and easy to care for.The furniture is both sturdy and lightweight as the frame is made of rustproof aluminum.You can easily sand down light scratches on the slates with fine sandpaper.",
			"images" => ["uploads/simplescraper/5329acc4378bb.jpg"]
		)

The config allows for the following options:

	// If defined, download the scraped images to this directory
	// PLEASE NOTE: use a dedicated folder if you want to use download_ttl
	'download_dir' => public_path().'/uploads/simplescraper/',
	
	// Delete downloded images after (seconds)
	// set to zero to prevent automatic cleanup
	'download_ttl' => 120, 
	
	// The maximum number of images to download
	'max_imgs' => 1,
	
	// Set a minimum size for the images that are shown.  This requires
	// a download_dir to be set.
	'minimum_size' => '300x200'


## Credits

Inspired by the Laraval 3 bundle Scrapey https://github.com/BKWLD/scrapey .
