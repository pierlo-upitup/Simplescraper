<?php
// todo
class CurlScraperTest extends PHPUnit_Framework_TestCase { 
    private $scraper;
/*
    public function setUp()
    {
        $config = array(
            'download_dir' => __DIR__.'/uploads/simplescraper/',
            'download_ttl' => 120, 
            'max_imgs' => 1,
            'minimum_size' => '300x200'
        );
        

        $this->scraper = new Negative\Simplescraper\CurlScraper($config);
    }

    public function test_method_doCurlExec()
    {

        $this->scraper->setURL('http://feynest.dev:8888/products/aida-leather');

        // Need to test privates here. 
        // Probably not a good idea?
        // 
        // http://sebastian-bergmann.de/archives/881-Testing-Your-Privates.html        
        $method = new ReflectionMethod(
          'Negative\Simplescraper\CurlScraper', 'doCurlExec'
        );
        $method->setAccessible(TRUE);
        $method->invoke($this->scraper);

        
    }
    */
   public function test_hello()
   {
    return true;
   }

}