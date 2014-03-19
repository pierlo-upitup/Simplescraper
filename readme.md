## Simplescraper

A (very) simple URL scraper that fetches title, description and images.

## Installation

Install via Composer

		composer require negative\simplescraper 

version: dev-master.

Add

		'Negative\Simplescraper\SimplescraperServiceProvider'

to your app/config.php $providers array

Please check vendor/negative/simplescraper/src/config/config.php for configuration options.

## Usage

Simply call:

		Simplescraper::lookup('http://www.ikea.com/us/en/catalog/products/60202199/');

It will return an array like this:

		array(
			"url" => "http:\/\/www.ikea.com\/us\/en\/catalog\/products\/60202199\/",
			"title" => "FALSTER Table - gray  - IKEA",
			"description" => "IKEA - FALSTER, Table, gray , Polystyrene slats are weather-resistant and easy to care for.The furniture is both sturdy and lightweight as the frame is made of rustproof aluminum.You can easily sand down light scratches on the slates with fine sandpaper.",
			"images" => ["uploads/simplescraper/5329acc4378bb.jpg"
		)

## Credits

Inspired by the Laraval 3 bundle Scrapey https://github.com/BKWLD/scrapey .