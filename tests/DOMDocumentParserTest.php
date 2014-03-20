<?php

use Negative\Simplescraper\DOMDocumentParser;

class DOMDocumentParserTest extends PHPUnit_Framework_TestCase {    
    
    private $parser;

    public function setUp()
    {
        $this->parser = new DOMDocumentParser();
    }

    public function test_it_parses_title()
    {
        // No meta tags, just a basic title in this case
        $html = '<html><head><title>Basic Title</title></head></html>';
        $url = '';

        $page = $this->parser->parse($html, $url);
        $this->assertArrayHasKey('title', $page);
        $this->assertEquals('Basic Title', $page['title']);
    }

    public function test_it_falls_back_to_url_if_no_title()
    {
        $html = '<html><head><meta name="description"content="No Title Attribute"/></head></html>';
        $url = 'http://www.google.com';

        $page = $this->parser->parse($html, $url);
        // If no title was found it should use the URL.        
        $this->assertArrayHasKey('title', $page);
        $this->assertEquals($url, $page['title']);
    }

    public function test_it_parses_title_and_description()
    {
        $html = '<html><head><title>Basic Title</title><meta name="description"content="Basic Description"/></head><body></body></html>';
        $url = '';

        $page = $this->parser->parse($html, $url);
        // Title
        $this->assertArrayHasKey('title', $page);
        $this->assertEquals('Basic Title', $page['title']);
        // Description
        $this->assertArrayHasKey('description', $page);
        $this->assertEquals('Basic Description', $page['description']);
    }
    
    public function test_it_parses_open_graph_title()
    {
        $html = '<html><head><title>Basic Title</title><meta content="OG Title" property="og:title" /><meta name="description" content="Basic Description"/></head></html>';
        $url = '';

        $page = $this->parser->parse($html, $url);
        $this->assertArrayHasKey('title', $page);
        $this->assertEquals('OG Title', $page['title']);
    }

    public function test_it_parses_open_graph_description()
    {
        $html = '<html><head><meta content="OG Description" property="og:description" /><meta name="description" content="Basic Description"/></head></html>';
        $url = '';

        $page = $this->parser->parse($html, $url);
        $this->assertArrayHasKey('description', $page);
        $this->assertEquals('OG Description', $page['description']);
    }

    public function test_it_parses_images_with_src_attribute_and_adds_url()
    {
        $html = '<img src="test.jpg" alt="Test" width="1" height="1"/><img alt="Empty" width="1" height="1"/>';
        $url = 'http://localhost';
        $page = $this->parser->parse($html, $url);
        $this->assertCount(1, $page['images']);
        $this->assertArrayHasKey('images', $page);
        $this->assertEquals('http://localhost/test.jpg', $page['images'][0]);

        $html = '<img src="http://localhost/test.jpg" alt="Test" width="1" height="1"/><img alt="Empty" width="1" height="1"/>';
        $url = 'http://localhost';
        $page = $this->parser->parse($html, $url);
        $this->assertCount(1, $page['images']);
        $this->assertArrayHasKey('images', $page);
        $this->assertEquals('http://localhost/test.jpg', $page['images'][0]);
    }
    
}