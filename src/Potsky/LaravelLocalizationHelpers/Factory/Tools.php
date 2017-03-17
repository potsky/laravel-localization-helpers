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

	/**
	 * Check if the "$dir_lang/$lang" is a valid directory
	 *
	 * @param string $dir_lang
	 * @param string $lang
	 *
	 * @return bool
	 */
	public static function isValidDirectory( $dir_lang , $lang )
	{
		if ( ! in_array( $lang , array( "." , ".." ) ) )
		{
			if ( is_dir( $dir_lang . DIRECTORY_SEPARATOR . $lang ) )
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * The escape char before a dot is used to escape all dots next to the escaped dot
	 *
	 * @param  array  $array
	 * @param  string $key
	 * @param  mixed  $value
	 * @param  string $regex
	 * @param  int    $level
	 *
	 * @return array
	 */
	public static function arraySet( &$array , $key , $value , $regex = '/\\./' , $level = -1 )
	{
		if ( is_null( $key ) )
		{
			return $array = $value;
		}

		$keys = preg_split( $regex , $key , $level );

		while ( count( $keys ) > 1 )
		{
			$key = array_shift( $keys );

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) )
			{
				$array[ $key ] = [ ];
			}

			$array = &$array[ $key ];
		}

		$array[ array_shift( $keys ) ] = $value;

		return $array;
	}


	/**
	 * Return char 's' if argument is greater than 1
	 *
	 * @param float|int|string $number
	 *
	 * @return string
	 */
	public static function getPlural( $number )
	{
		return ( (float)$number >= 2 ) ? 's' : '';
	}


	/**
	 * Remove all whitesapces, line-breaks, and tabs from string for better regex recognition
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function minifyString( $string )
	{
		$string = str_replace( PHP_EOL , ' ' , $string );
		$string = preg_replace( '/[\r\n]+/' , "\n" , $string );

		return preg_replace( '/[ \t]+/' , ' ' , $string );
	}

}