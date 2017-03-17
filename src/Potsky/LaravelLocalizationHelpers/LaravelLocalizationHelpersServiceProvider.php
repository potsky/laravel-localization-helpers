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
	 *
	 * @codeCoverageIgnore
	 */
	public function boot()
	{
		if ( function_exists( "config_path" ) )
		{
			$this->publishes( array(
				__DIR__ . '/../../config/config-laravel5.php' => config_path( 'laravel-localization-helpers.php' ) ,
			) );
		}
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
			'localization.command.find' ,
			'localization.command.clear'
		);

		$this->app[ 'localization.helpers' ] = $this->app->share( function ( $app )
		{
			return new Factory\Localization( new Factory\MessageBag() );
		} );

		$this->mergeConfigFrom(
			__DIR__ . '/../../config/config-laravel5.php' , 'laravel-localization-helpers'
		);
	}

}




