<?php namespace Potsky\LaravelLocalizationHelpers;

class LaravelLocalizationHelpersServiceProvider extends LaravelLocalizationHelpersServiceProviderAbstract
{

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

		$this->package( 'potsky/laravel-localization-helpers' );
	}

}



