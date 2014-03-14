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
            // $view = Config::get('utilities::config.bind_js_vars_to_this_view');
            // $namespace = Config::get('utilities::config.js_namespace');
        	$config = Config::get('simplescraper::config');

            // $binder = new LaravelViewBinder($app['events'], $view);
        	$scraper = new CurlScraper();
            return new Simplescraper($scraper, $config);
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