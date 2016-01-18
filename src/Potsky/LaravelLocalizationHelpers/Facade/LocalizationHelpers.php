<?php namespace Potsky\LaravelLocalizationHelpers\Facade;

use Illuminate\Support\Facades\Facade;

class LocalizationHelpers extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'localization.helpers';
	}
}