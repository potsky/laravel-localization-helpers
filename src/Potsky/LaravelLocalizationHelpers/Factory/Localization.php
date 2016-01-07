<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

class Localization
{
	public function __construct()
	{
	}

	/**
	 * Get the lang directory path
	 *
	 * @param $lang_folder_path
	 *
	 * @return string the path
	 * @throws \Potsky\LaravelLocalizationHelpers\Factory\Exception
	 */
	public function getLangPath( $lang_folder_path = null )
	{
		if ( empty( $lang_folder_path ) )
		{
			$paths = array(
				app_path() . DIRECTORY_SEPARATOR . 'lang' ,
				base_path() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang' ,
			);

			foreach ( $paths as $path )
			{
				if ( file_exists( $path ) )
				{
					return $path;
				}
			}

			$e = new Exception( '' , Exception::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS );
			$e->setParameters( $paths );
			throw $e;
		}
		else
		{
			if ( file_exists( $lang_folder_path ) )
			{
				return $lang_folder_path;
			}

			$e = new Exception( '' , Exception::NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH );
			$e->setParameters( $lang_folder_path );
			throw $e;
		}
	}


	/**
	 * Return an absolute path without predefined variables
	 *
	 * @param string|array $path the relative path
	 *
	 * @return array the absolute path
	 */
	public function getPath( $path )
	{
		if ( ! is_array( $path ) )
		{
			$path = array( $path );
		}

		$folders = str_replace(
			array(
				'%APP' ,
				'%BASE' ,
				'%PUBLIC' ,
				'%STORAGE' ,
			) ,
			array(
				app_path() ,
				base_path() ,
				public_path() ,
				storage_path() ,
			) ,
			$path
		);

		foreach ( $folders as $k => $v )
		{
			$folders[ $k ] = realpath( $v );
		}

		return $folders;
	}


	/**
	 * Return an relative path to the laravel directory
	 *
	 * @param string $path the absolute path
	 *
	 * @return string the relative path
	 */
	public function getShortPath( $path )
	{
		return str_replace( base_path() , '' , $path );
	}


	/**
	 * Return an iterator of files with specific extension in the provided paths and subpaths
	 *
	 * @param string $path a source path
	 *
	 * @return array a list of file paths
	 */
	public function getFilesWithExtension( $path , $ext = 'php' )
	{
		if ( is_dir( $path ) )
		{
			return new \RegexIterator(
				new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $path , \RecursiveDirectoryIterator::SKIP_DOTS ) ,
					\RecursiveIteratorIterator::SELF_FIRST ,
					\RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
				) ,
				'/^.+\.' . $ext . '$/i' ,
				\RecursiveRegexIterator::GET_MATCH
			);
		}
		else
		{
			return array();
		}
	}


	/**
	 * Extract all translations from the provided file
	 * Remove all translations containing :
	 * - $  -> auto-generated translation cannot be supported
	 * - :: -> package translations are not taken in account
	 *
	 * @param string $path          the file path
	 * @param array  $trans_methods an array of regex to catch
	 *
	 * @return array an array dot of found translations
	 */
	public function extractTranslationFromPhpFile( $path , $trans_methods )
	{
		$result = array();
		$string = file_get_contents( $path );

		foreach ( array_flatten( $trans_methods ) as $method )
		{
			preg_match_all( $method , $string , $matches );

			foreach ( $matches[ 1 ] as $k => $v )
			{
				if ( strpos( $v , '$' ) !== false )
				{
					unset( $matches[ 1 ][ $k ] );
				}
				if ( strpos( $v , '::' ) !== false )
				{
					unset( $matches[ 1 ][ $k ] );
				}
			}
			$result = array_merge( $result , array_flip( $matches[ 1 ] ) );
		}

		return $result;
	}

}


