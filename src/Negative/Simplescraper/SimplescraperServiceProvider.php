<?php namespace Negative\Simplescraper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

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
	
	public function boot()
	{
		$this->package('negative/simplescraper');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		
		// TODO understand what this does...
		$this->app['simplescraper'] = $this->app->share(function($app){
			return new Simplescraper($app['view']);
		});

		$this->app->booting(function(){
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Simplescraper', 'Negative\Simplescraper\Facades\Simplescraper');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('simplescraper');
	}

}
