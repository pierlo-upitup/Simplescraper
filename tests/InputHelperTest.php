<?php

use Negative\Simplescraper\Helpers\InputHelper;

class InputHelperTest extends PHPUnit_Framework_TestCase { 
    
    // Make sure http:// is prepended    
    public function test_it_adds_trailing_scheme()
    {
        $valid_url = 'http://www.google.it';
        $invalid_url = 'www.google.it';
        $this->assertSame($valid_url, InputHelper::sanitizeUrl($invalid_url));
    }

    // Make sure it strips out bad characters but still works
    public function test_it_corrects_bad_input()
    {
        $valid_url = 'http://www.google.it';
        $invalid_url = '°www.goog��le.it�§';
        $this->assertSame($valid_url, InputHelper::sanitizeUrl($invalid_url));
    }

    // Check it fails when input cannot form a valid URL 
    // according to RFC2396.
    public function test_detects_if_not_a_url()
    {
        // Underscores are disallowed
        $this->assertFalse(InputHelper::sanitizeUrl('not_a_url'));
        // Empty string
        $this->assertFalse(InputHelper::sanitizeUrl(' '));        
    }

    public function test_can_handle_tricky_input()
    {
        // The following are all "valid" URLs
        $this->assertNotEquals(false, InputHelper::sanitizeUrl('8.8.8.8'));
        $this->assertNotEquals(false, InputHelper::sanitizeUrl('localhost'));
    }
}