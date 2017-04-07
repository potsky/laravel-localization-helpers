<?php

namespace Potsky\LaravelLocalizationHelpers\Factory;


use Potsky\LaravelLocalizationHelpers\Object\LangFileTypeGenuine;
use Potsky\LaravelLocalizationHelpers\Object\LangFileTypeGenuineVendor;
use Potsky\LaravelLocalizationHelpers\Object\LangFileTypeJson;

class LangFile
{

	/**
	 * These subfolders are reserved for vendors
	 *
	 * https://laravel.com/docs/4.2/localization
	 * https://laravel.com/docs/5.1/localization
	 *
	 * @var array
	 */
	protected static $vendorsFolders = array( 'vendor' , 'packages' );


	/**
	 * @param string $dir_lang
	 * @param array  $json_langs
	 *
	 * @return array
	 */
	public static function getLangFiles( $dir_lang , $json_langs = null )
	{
		$langs = array();

		// scan genuine folders first
		foreach ( scandir( $dir_lang ) as $lang )
		{
			if ( ! Tools::isValidDirectory( $dir_lang , $lang ) )
			{
				continue;
			}

			if ( in_array( $lang , self::$vendorsFolders ) )
			{
				$langs[] = new LangFileTypeGenuineVendor( $lang );
			}
			else
			{
				$langs[] = new LangFileTypeGenuine( $lang );
			}

		}

		// generate json langs
		if ( is_array( $json_langs ) )
		{
			foreach ( $json_langs as $lang )
			{
				$langs[] = new LangFileTypeJson( $lang );
			}
		}

		return $langs;
	}
}
