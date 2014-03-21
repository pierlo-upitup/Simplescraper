<?php
// todo
class CurlScraperTest extends PHPUnit_Framework_TestCase { 
    private $scraper;

    public function setUp()
    {
        // Skip image downloads for now
        $config = array(
            'download_dir' => '',
            'download_ttl' => 0, 
            'max_imgs' => 0,
            'minimum_size' => '1x1'
        );
        $this->scraper = new Negative\Simplescraper\CurlScraper($config);
    }

    public function test_it_fetches_url()
    {

        $this->assertContains('Google', $this->scraper->fetch('https://www.google.com'));
    }
    
  
}