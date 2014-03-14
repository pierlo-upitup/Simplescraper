<?php namespace Negative\Simplescraper;


/**
 * Maybe a bit overkill...
 */
class Response extends \Eloquent {

	public $HTML;
	
	private $images;

	public function __construct(opengraph\OpenGraph $graph)
	{
		$this->title = $graph->title;
		$this->description = $graph->description;
		$this->HTML = $graph->HTML;
	}

}