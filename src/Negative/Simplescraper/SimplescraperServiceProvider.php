<?php namespace Negative\Simplescraper;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

use Config;

class SimplescraperServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Simplescraper', function($app) { 
        	// Get configuration from package 
        	$config = Config::get('simplescraper::config');
            return new Simplescraper($config);
        });
    }

    public function boot()
    {
        $this->package('negative/simplescraper');
        AliasLoader::getInstance()->alias('Simplescraper', 'Negative\Simplescraper\Facades\Simplescraper');
    }


    /**
     * The service provided
     *
     * @return array
     */
    public function provides()
    {
        return ['Simplescraper'];
    }

}