<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

class Localization
{
	const NO_LANG_FOLDER_FOUND_IN_THESE_PATHS      = 2;
	const NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH = 3;
	const BACKUP_DATE_FORMAT                       = "Ymd_His";

	/** @var MessageBagInterface $messageBag */
	private $messageBag;

	/**
	 * @param \Potsky\LaravelLocalizationHelpers\Factory\MessageBagInterface $messageBag A message bag or a Console
	 *                                                                                   object for output reports
	 */
	public function __construct( MessageBagInterface $messageBag )
	{
		$this->messageBag = $messageBag;
	}

	/**
	 * Get the current used message bag for facades essentially
	 *
	 * @return \Potsky\LaravelLocalizationHelpers\Factory\MessageBagInterface
	 */
	public function getMessageBag()
	{
		return $this->messageBag;
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

			$e = new Exception( '' , self::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS );
			$e->setParameter( $paths );
			throw $e;
		}
		else
		{
			if ( file_exists( $lang_folder_path ) )
			{
				return $lang_folder_path;
			}

			$e = new Exception( '' , self::NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH );
			$e->setParameter( $lang_folder_path );
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
	 * @param string $ext
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
	 *
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

	/**
	 * Extract all translations from the provided folders
	 *
	 * @param array $folders       a list of folder to search in
	 * @param array $trans_methods an array of regex to catch
	 *
	 * @return array
	 */
	public function extractTranslationsFromFolders( $folders , $trans_methods , $php_file_extension = 'php' )
	{
		$lemmas = array();

		foreach ( $folders as $path )
		{
			foreach ( $this->getFilesWithExtension( $path , $php_file_extension ) as $php_file_path => $dumb )
			{
				$lemma = array();

				foreach ( $this->extractTranslationFromPhpFile( $php_file_path , $trans_methods ) as $k => $v )
				{
					$real_value           = eval( "return $k;" );
					$lemma[ $real_value ] = $php_file_path;
				}

				$lemmas = array_merge( $lemmas , $lemma );
			}
		}

		return $lemmas;
	}

	/**
	 * @param array $lemmas an array of lemma
	 *                      eg: [ 'message.lemma.child' => string(83)
	 *                      "/Users/potsky/WTF/laravel-localization-helpers/tests/mock/trans.php" , ... ]
	 *
	 * @return array a structured array of lemma
	 *               eg: array(1) {
	 *                        'message' =>
	 *                            array(2) {
	 *                            'lemma' =>
	 *                                 array(9) {
	 *                                    'child' => string(83)
	 *                                    "/Users/potsky/Work/Private/GitHub/laravel-localization-helpers/tests/mock/trans.php"
	 *                        ...
	 */
	public function convertLemmaToStructuredArray( $lemmas )
	{
		$lemmas_structured = array();

		foreach ( $lemmas as $key => $value )
		{
			if ( strpos( $key , '.' ) === false )
			{
				$this->messageBag->writeLine( '    <error>' . $key . '</error> in file <comment>' . $this->getShortPath( $value ) . '</comment> <error>will not be included because it has no family</error>' );
			}
			else
			{
				array_set( $lemmas_structured , $key , $value );
			}
		}

		return $lemmas_structured;
	}

	/**
	 * @param array $lemmas an array of lemma
	 *                      eg: [ 'message.lemma.child' => string(83)
	 *                      "/Users/potsky/WTF/laravel-localization-helpers/tests/mock/trans.php" , ... ]
	 *
	 * @return array a flat array of lemma
	 *               eg: array(1) {
	 *                        'message' =>
	 *                            array(2) {
	 *                            'lemma.child' => string(83)
	 *                            "/Users/potsky/Work/Private/GitHub/laravel-localization-helpers/tests/mock/trans.php"
	 *                        ...
	 */
	public function convertLemmaToFlatArray( $lemmas )
	{
		$lemmas_structured = array();

		foreach ( $lemmas as $key => $value )
		{
			if ( strpos( $key , '.' ) === false )
			{
				$this->messageBag->writeLine( '    <error>' . $key . '</error> in file <comment>' . $this->getShortPath( $value ) . '</comment> <error>will not be included because it has no family</error>' );
			}
			else
			{
				Tools::arraySetDotFirstLevel( $lemmas_structured , $key , $value );
			}
		}

		return $lemmas_structured;
	}

	/**
	 * @param int $offsetDay the count of days to subtract to the current time
	 *
	 * @return bool|string current date
	 */
	public function getBackupDate( $offsetDay = 0 )
	{
		$now = new \DateTime();
		$now->sub( new \DateInterval( 'P' . (int)$offsetDay . 'D' ) );

		return $now->format( self::BACKUP_DATE_FORMAT );
	}

	/**
	 * Return all lang backup files
	 *
	 * @param string $lang_directory the lang directory
	 *
	 * @return array
	 */
	public function getBackupFiles( $lang_directory )
	{
		$files = $lang_directory . '/*/*[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]_[0-9][0-9][0-9][0-9][0-9][0-9].php';

		return glob( $files );
	}


	public function deleteBackupFiles( $lang_folder_path , $days = 0 , $dryRun = false )
	{
		try
		{
			$dir_lang = $this->getLangPath( $lang_folder_path );
		}
		catch ( Exception $e )
		{
			switch ( $e->getCode() )
			{
				case self::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS:
					$this->messageBag->writeError( "No lang folder found in these paths:" );
					foreach ( $e->getParameter() as $path )
					{
						$this->messageBag->writeError( "- " . $path );
					}
					break;

				case self::NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH:
					$this->messageBag->writeError( 'No lang folder found in your custom path: "' . $e->getParameter() . '"' );
					break;
			}

			return false;
		}

		$return = true;

		foreach ( $this->getBackupFiles( $dir_lang ) as $file )
		{
			if ( $this->isBackupFileOlderThanDays( $file , $days ) )
			{
				if ( $dryRun === true )
				{
					$deleted = true;
				}
				else
				{
					$deleted = unlink( $file );
				}

				if ( $deleted === true )
				{
					$this->messageBag->writeInfo( 'Deleting file ' . $file );
				}
				else
				{
					$this->messageBag->writeError( 'Unable to delete file ' . $file );

					$return = false;
				}
			}
			else
			{
				$this->messageBag->writeInfo( 'Skip file ' . $file . ' (not older than ' . $days . 'day' . Tools::getPlural( $days ) . ')' );
			}

		}

		return $return;
	}

	public function isBackupFileOlderThanDays( $file , $days )
	{
		$fileDate = $this->getBackupFileDate( $file );
//TODO::
		return false;
	}

	/**
	 * Return the date of a backup file
	 *
	 * @param string $file a backup file path
	 *
	 * @return \DateTime|null
	 */
	private function getBackupFileDate( $file )
	{
		$matches = array();

		if ( preg_match( '@^(.*)([0-9]{8}_[0-9]{6})\\.php$@' , $file , $matches ) === 1 )
		{
			return \DateTime::createFromFormat( self::BACKUP_DATE_FORMAT , $matches[2] );
		}
		else
		{
			return null;
		}
	}

}


