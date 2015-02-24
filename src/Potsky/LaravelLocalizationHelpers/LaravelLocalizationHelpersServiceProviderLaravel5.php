<?php namespace Potsky\LaravelLocalizationHelpers;

use Illuminate\Support\ServiceProvider;

class LaravelLocalizationHelpersServiceProviderLaravel5 extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
        $this->publishes(array(
            __DIR__.'/../../config/config-laravel5.php' => config_path('laravel-localization-helpers.php')
        ));
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['localization.missing'] = $this->app->share( function( $app ) {
        	return new Commands\LocalizationMissing( $app['config'] );
    	});

		$this->app['localization.find'] = $this->app->share( function( $app ) {
        	return new Commands\LocalizationFind( $app['config'] );
    	});

    	$this->commands(
    		'localization.missing',
    		'localization.find'
    	);
		
		$this->mergeConfigFrom(
			 __DIR__.'/../../config/config-laravel5.php', 'laravel-localization-helpers'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}



