<?php namespace Potsky\LaravelLocalizationHelpers;

class LaravelLocalizationHelpersServiceProviderLaravel5 extends LaravelLocalizationHelpersServiceProviderAbstract
{
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function boot()
	{
		parent::boot();

		$this->publishes( array(
			__DIR__ . '/../../config/config-laravel5.php' => config_path( 'laravel-localization-helpers.php' ) ,
		) );
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function register()
	{
		parent::register();

		$this->mergeConfigFrom(
			__DIR__ . '/../../config/config-laravel5.php' , 'laravel-localization-helpers'
		);
	}

}



