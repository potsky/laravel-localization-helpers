<?php

namespace Potsky\LaravelLocalizationHelpers\Factory;


class Tools
{
	/**
	 * @return int
	 */
	public static function getLaravelMajorVersion()
	{
		$versions = explode( '.' , self::getLaravelVersion() , 1 );

		return @(int)$versions[ 0 ];
	}

	/**
	 * @return string
	 */
	public static function getLaravelVersion()
	{
		$laravel = app();

		return strval( $laravel::VERSION );
	}

	/**
	 * @return bool
	 */
	public static function isLaravel5()
	{
		return ( self::getLaravelMajorVersion() === 5 );
	}


	/**
	 * @param string $glob a file glob
	 *
	 * @return array the list of deleted files
	 */
	public static function unlinkGlobFiles( $glob )
	{
		$files  = glob( $glob );
		$return = array();

		foreach ( $files as $file )
		{
			if ( ! is_dir( $file ) )
			{
				if ( unlink( $file ) === true )
				{
					$return[] = $file;
				}
			}
		}

		return $return;
	}

}