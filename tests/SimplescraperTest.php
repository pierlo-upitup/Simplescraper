<?php

class SimplescraperTest extends PHPUnit_Framework_TestCase {
    
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
        $this->scraper = new Negative\Simplescraper\Simplescraper($config);
    }

    public function test_it_does_what_it_says()
    {
        $url = 'http://github.com/pierlo-upitup/Simplescraper';
        $expected = array(
            'url' => 'https://github.com/pierlo-upitup/Simplescraper',
            'title' => 'pierlo-upitup/Simplescraper',
            'site_name' => 'GitHub',
            'type' => 'object',
            'image' => 'https://avatars1.githubusercontent.com/u/1078545?s=400',
            'description' => 'Simplescraper - A (very) simple URL scraper that fetches title, description and images.',
            'images' => array()
        );
        $this->assertSame($expected, $this->scraper->lookup($url));
    }
}