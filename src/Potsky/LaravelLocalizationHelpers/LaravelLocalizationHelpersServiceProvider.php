<?php namespace Potsky\LaravelLocalizationHelpers;

use Illuminate\Support\ServiceProvider;

class LaravelLocalizationHelpersServiceProvider extends ServiceProvider
{
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
		$this->package( 'potsky/laravel-localization-helpers' );
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app[ 'localization.command.missing' ] = $this->app->share( function ( $app )
		{
			return new Command\LocalizationMissing( $app[ 'config' ] );
		} );

		$this->app[ 'localization.command.find' ] = $this->app->share( function ( $app )
		{
			return new Command\LocalizationFind( $app[ 'config' ] );
		} );

		$this->app[ 'localization.command.clear' ] = $this->app->share( function ( $app )
		{
			return new Command\LocalizationClear( $app[ 'config' ] );
		} );

		$this->commands(
			'localization.command.missing' ,
			'localization.command.find',
			'localization.command.clear'
		);

		$this->app[ 'localization.helpers' ] = $this->app->share( function ()
		{
			return new Factory\Localization( new Factory\MessageBag() );
		} );
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



