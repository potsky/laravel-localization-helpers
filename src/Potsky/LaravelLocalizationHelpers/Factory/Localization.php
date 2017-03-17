<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

use Config;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Localization
{
	const NO_LANG_FOLDER_FOUND_IN_THESE_PATHS      = 2;
	const NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH = 3;
	const BACKUP_DATE_FORMAT                       = "Ymd_His";
	const PREFIX_LARAVEL_CONFIG                    = 'laravel-localization-helpers.';

	private static $PHP_CS_FIXER_LEVELS = array( 'psr0' , 'psr1' , 'psr2' , 'symfony' );
	private static $PHP_CS_FIXER_FIXERS = array(
		'psr0' ,
		'encoding' ,
		'short_tag' ,
		'braces' ,
		'elseif' ,
		'eof_ending' ,
		'function_call_space' ,
		'function_declaration' ,
		'indentation' ,
		'line_after_namespace' ,
		'linefeed' ,
		'lowercase_constants' ,
		'lowercase_keywords' ,
		'method_argument_space' ,
		'multiple_use' ,
		'parenthesis' ,
		'php_closing_tag' ,
		'single_line_after_imports' ,
		'trailing_spaces' ,
		'visibility' ,
		'array_element_no_space_before_comma' ,
		'array_element_white_space_after_comma' ,
		'blankline_after_open_tag' ,
		'concat_without_spaces' ,
		'double_arrow_multiline_whitespaces' ,
		'duplicate_semicolon' ,
		'empty_return' ,
		'extra_empty_lines' ,
		'function_typehint_space' ,
		'include' ,
		'join_function' ,
		'list_commas' ,
		'multiline_array_trailing_comma' ,
		'namespace_no_leading_whitespace' ,
		'new_with_braces' ,
		'no_blank_lines_after_class_opening' ,
		'no_empty_lines_after_phpdocs' ,
		'object_operator' ,
		'operators_spaces' ,
		'phpdoc_indent' ,
		'phpdoc_inline_tag' ,
		'phpdoc_no_access' ,
		'phpdoc_no_empty_return' ,
		'phpdoc_no_package' ,
		'phpdoc_params' ,
		'phpdoc_scalar' ,
		'phpdoc_separation' ,
		'phpdoc_short_description' ,
		'phpdoc_to_comment' ,
		'phpdoc_trim' ,
		'phpdoc_type_to_var' ,
		'phpdoc_types' ,
		'phpdoc_var_without_name' ,
		'pre_increment' ,
		'print_to_echo' ,
		'remove_leading_slash_use' ,
		'remove_lines_between_uses' ,
		'return' ,
		'self_accessor' ,
		'short_bool_cast' ,
		'single_array_no_trailing_comma' ,
		'single_blank_line_before_namespace' ,
		'single_quote' ,
		'spaces_before_semicolon' ,
		'spaces_cast' ,
		'standardize_not_equal' ,
		'ternary_spaces' ,
		'trim_array_spaces' ,
		'unalign_double_arrow' ,
		'unalign_equals' ,
		'unary_operators_spaces' ,
		'unneeded_control_parentheses' ,
		'unused_use' ,
		'whitespacy_lines' ,
		'align_double_arrow' ,
		'align_equals' ,
		'concat_with_spaces' ,
		'echo_to_print' ,
		'ereg_to_preg' ,
		'header_comment' ,
		'logical_not_operators_with_spaces' ,
		'logical_not_operators_with_successor_space' ,
		'long_array_syntax' ,
		'multiline_spaces_before_semicolon' ,
		'newline_after_open_tag' ,
		'no_blank_lines_before_namespace' ,
		'ordered_use' ,
		'php4_constructor' ,
		'php_unit_construct' ,
		'php_unit_strict' ,
		'phpdoc_order' ,
		'phpdoc_var_to_type' ,
		'short_array_syntax' ,
		'short_echo_tag' ,
		'strict' ,
		'strict_param' ,
	);

	/** @var TranslatorInterface $translator */
	protected $translator;

	/** @var MessageBagInterface $messageBag */
	protected $messageBag;

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
				base_path() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang' ,
			);

			if ( function_exists( 'app_path' ) )
			{
				$paths[] = app_path() . DIRECTORY_SEPARATOR . 'lang';
			}

			foreach ( $paths as $path )
			{
				if ( file_exists( $path ) )
				{
					return $path;
					//@codeCoverageIgnoreStart
				}
			}

			$e = new Exception( '' , self::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS );
			$e->setParameter( $paths );
			throw $e;
			//@codeCoverageIgnoreEnd
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

		$search_for = array(
			'%BASE' ,
			'%STORAGE' ,
		);

		$replace_by = array(
			base_path() ,
			storage_path() ,
		);

		if ( function_exists( 'app_path' ) )
		{
			$search_for[] = '%APP';
			$replace_by[] = app_path();
		}

		if ( function_exists( 'public_path' ) )
		{
			$search_for[] = '%PUBLIC';
			$replace_by[] = public_path();
		}

		$folders = str_replace( $search_for , $replace_by , $path );

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
		$string = Tools::minifyString( file_get_contents( $path ) );

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
	 * @param array  $folders            a list of folder to search in
	 * @param array  $trans_methods      an array of regex to catch
	 * @param string $php_file_extension default is php
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
					$a = $this->evalString( $k );
					if ( is_string( $a ) )
					{
						$real_value           = $a;
						$lemma[ $real_value ] = $php_file_path;
					}
					else
					{
						$this->messageBag->writeError( "Unable to understand string $k" );
					}
				}

				$lemmas = array_merge( $lemmas , $lemma );
			}
		}

		return $lemmas;
	}


	/**
	 * @param array  $lemmas an array of lemma
	 *                       eg: [ 'message.lemma.child' => string(83)
	 *                       "/Users/potsky/WTF/laravel-localization-helpers/tests/mock/trans.php" , ... ]
	 *
	 * @param string $dot_notation_split_regex
	 * @param int    $level
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
	public function convertLemmaToStructuredArray( $lemmas , $dot_notation_split_regex , $level = -1 )
	{
		$lemmas_structured = array();

		if ( ! is_string( $dot_notation_split_regex ) )
		{
			// fallback to dot if provided regex is not a string
			$dot_notation_split_regex = '/\\./';
		}

		foreach ( $lemmas as $key => $value )
		{
			if ( strpos( $key , '.' ) === false )
			{
				$this->messageBag->writeLine( '    <error>' . $key . '</error> in file <comment>' . $this->getShortPath( $value ) . '</comment> <error>will not be included because it has no family</error>' );
			}
			else
			{
				Tools::arraySet( $lemmas_structured , $key , $value , $dot_notation_split_regex , $level );
			}
		}

		return $lemmas_structured;
	}

	/**
	 * @param array  $lemmas an array of lemma
	 *                       eg: [ 'message.lemma.child' => string(83)
	 *                       "/Users/potsky/WTF/laravel-localization-helpers/tests/mock/trans.php" , ... ]
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
		return $this->convertLemmaToStructuredArray( $lemmas , null , null , 2 );
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
	 * @param string $ext
	 *
	 * @return array
	 */
	public function getBackupFiles( $lang_directory , $ext = 'php' )
	{
		$files = $lang_directory . '/*/*[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]_[0-9][0-9][0-9][0-9][0-9][0-9].' . $ext;

		return glob( $files );
	}


	/**
	 * Delete backup files
	 *
	 * @param string     $lang_folder_path
	 * @param int        $days
	 * @param bool|false $dryRun
	 * @param string     $ext
	 *
	 * @return bool
	 */
	public function deleteBackupFiles( $lang_folder_path , $days = 0 , $dryRun = false , $ext = 'php' )
	{
		if ( $days < 0 )
		{
			return false;
		}

		try
		{
			$dir_lang = $this->getLangPath( $lang_folder_path );
		}
		catch ( Exception $e )
		{
			switch ( $e->getCode() )
			{
				//@codeCoverageIgnoreStart
				case self::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS:
					$this->messageBag->writeError( "No lang folder found in these paths:" );
					foreach ( $e->getParameter() as $path )
					{
						$this->messageBag->writeError( "- " . $path );
					}
					break;
				//@codeCoverageIgnoreEnd

				case self::NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH:
					$this->messageBag->writeError( 'No lang folder found in your custom path: "' . $e->getParameter() . '"' );
					break;
			}

			return false;
		}

		$return = true;

		foreach ( $this->getBackupFiles( $dir_lang ) as $file )
		{
			$fileDate = $this->getBackupFileDate( $file , $ext );

			// @codeCoverageIgnoreStart
			// Cannot happen because of glob but safer
			if ( is_null( $fileDate ) )
			{
				$this->messageBag->writeError( 'Unable to detect date in file ' . $file );
				$return = false;

				continue;
			}
			// @codeCoverageIgnoreEnd

			if ( $this->isDateOlderThanDays( $fileDate , $days ) )
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
				// @codeCoverageIgnoreStart
				else
				{
					$this->messageBag->writeError( 'Unable to delete file ' . $file );

					$return = false;
				}
			}
			// @codeCoverageIgnoreEnd
			else
			{
				$this->messageBag->writeInfo( 'Skip file ' . $file . ' (not older than ' . $days . 'day' . Tools::getPlural( $days ) . ')' );
			}

		}

		return $return;
	}

	/**
	 * @param \DateTime $date
	 * @param int       $days
	 *
	 * @return bool
	 */
	public function isDateOlderThanDays( \DateTime $date , $days )
	{
		$now = new \DateTime();

		return ( $now->diff( $date )->format( '%a' ) >= $days );
	}


	/**
	 * Eval a PHP string and catch PHP Parse Error syntax
	 *
	 * @param $str
	 *
	 * @return bool|mixed
	 */
	private function evalString( $str )
	{
		$a = false;

		if ( class_exists( 'ParseError' ) )
		{
			try
			{
				$a = eval( "return $str;" );
			}
			catch ( \ParseError $e )
			{
			}
		}
		else
		{
			$a = @eval( "return $str;" );
		}

		return $a;
	}


	/**
	 * Get the list of PHP code files where a lemma is defined
	 *
	 * @param string     $lemma         A lemma to search for or a regex to search for
	 * @param array      $folders       An array of folder to search for lemma in
	 * @param array      $trans_methods An array of PHP lang functions
	 * @param bool|false $regex         Is lemma a regex ?
	 * @param bool|false $shortOutput   Output style for file paths
	 * @param string     $ext
	 *
	 * @return array|false
	 */
	public function findLemma( $lemma , $folders , $trans_methods , $regex = false , $shortOutput = false , $ext = 'php' )
	{
		$files = array();

		foreach ( $folders as $path )
		{
			foreach ( $this->getFilesWithExtension( $path , $ext ) as $php_file_path => $dumb )
			{
				foreach ( $this->extractTranslationFromPhpFile( $php_file_path , $trans_methods ) as $k => $v )
				{
					$a = $this->evalString( $k );
					if ( is_string( $a ) )
					{
						$real_value = $a;
						$found      = false;

						if ( $regex )
						{
							try
							{
								$r = preg_match( $lemma , $real_value );
							}
								// Exception is thrown via command
							catch ( \Exception $e )
							{
								$this->messageBag->writeError( "The argument is not a valid regular expression:" . str_replace( 'preg_match():' , '' , $e->getMessage() ) );

								return false;
							}
							if ( $r === 1 )
							{
								$found = true;
							}
							// Normal behavior via method call
							// @codeCoverageIgnoreStart
							else if ( $r === false )
							{
								$this->messageBag->writeError( "The argument is not a valid regular expression" );

								return false;
							}
							// @codeCoverageIgnoreEnd
						}
						else
						{
							if ( ! ( strpos( $real_value , $lemma ) === false ) )
							{
								$found = true;
							}
						}

						if ( $found === true )
						{
							if ( $shortOutput === true )
							{
								$php_file_path = $this->getShortPath( $php_file_path );
							}
							$files[] = $php_file_path;
							break;
						}
					}
					else
					{
						$this->messageBag->writeError( "Unable to understand string $k" );
					}
				}
			}
		}

		return $files;
	}

	/**
	 * @param string $word
	 * @param string $to
	 * @param null   $from
	 *
	 * @return mixed
	 */
	public function translate( $word , $to , $from = null )
	{
		if ( is_null( $this->translator ) )
		{
			$translator       = config( self::PREFIX_LARAVEL_CONFIG . 'translator' );
			$this->translator = new Translator( 'Microsoft' , array(
				'client_id'        => config( self::PREFIX_LARAVEL_CONFIG . 'translators.' . $translator . '.client_id' ) ,
				'client_secret'    => config( self::PREFIX_LARAVEL_CONFIG . 'translators.' . $translator . '.client_secret' ) ,
				'default_language' => config( self::PREFIX_LARAVEL_CONFIG . 'translators.' . $translator . '.default_language' ) ,
			) );
		}

		$translation = $this->translator->translate( $word , $to , $from );

		if ( is_null( $translation ) )
		{
			$translation = $word;
		}

		return $translation;
	}

	/**
	 * Fix Code Style for a file or a directory
	 *
	 * @throws \Exception
	 * @throws \Potsky\LaravelLocalizationHelpers\Factory\Exception
	 */
	public function fixCodeStyle( $filePath , array $fixers , $level = null )
	{
		if ( ( defined( 'HHVM_VERSION_ID' ) ) && ( HHVM_VERSION_ID < 30500 ) )
			// @codeCoverageIgnoreStart
		{
			throw new Exception( "HHVM needs to be a minimum version of HHVM 3.5.0" );
		}
		// @codeCoverageIgnoreEnd

		elseif ( ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < 50306 )
			// @codeCoverageIgnoreStart
		{
			throw new Exception( "PHP needs to be a minimum version of PHP 5.3.6" );
		}
		// @codeCoverageIgnoreEnd

		if ( ! file_exists( $filePath ) )
		{
			throw new Exception( 'File "' . $filePath . '" does not exist, cannot fix it' );
		}

		$options = array(
			'--no-interaction' => true ,
			'command'          => 'fix' ,
			'path'             => $filePath ,
		);

		$fix = array();
		foreach ( $fixers as $fixer )
		{
			if ( $this->isAFixer( $fixer ) )
			{
				$fix[] = $fixer;
			}
		}
		$options[ '--fixers' ] = implode( ',' , $fix );

		if ( $this->isALevel( $level ) )
		{
			$options[ '--level' ] = $level;
		}

		$input       = new ArrayInput( $options );
		$output      = new BufferedOutput();
		$application = new \Symfony\CS\Console\Application();
		$application->setAutoExit( false );
		$application->run( $input , $output );

		return $output->fetch();
	}

	/**
	 * Tell if the provided fixer is a valid fixer
	 *
	 * @param string $fixer
	 *
	 * @return bool
	 */
	public function isAFixer( $fixer )
	{
		return in_array( $fixer , self::$PHP_CS_FIXER_FIXERS );
	}

	/**
	 * Tell if the provided level is a valid level
	 *
	 * @param string $level
	 *
	 * @return bool
	 */
	public function isALevel( $level )
	{
		return in_array( $level , self::$PHP_CS_FIXER_LEVELS );
	}

	/**
	 * Get the backup file path according to the current file path
	 *
	 * @param string $file_lang_path
	 * @param string $date
	 * @param string $ext
	 *
	 * @return mixed
	 */
	public function getBackupPath( $file_lang_path , $date , $ext = 'php' )
	{
		return preg_replace( '/\.' . $ext . '$/' , '.' . $date . '.' . $ext , $file_lang_path );
	}

	/**
	 * Return the date of a backup file
	 *
	 * @param string $file a backup file path
	 * @param string $ext
	 *
	 * @return \DateTime|null
	 */
	private function getBackupFileDate( $file , $ext = 'php' )
	{
		$matches = array();

		if ( preg_match( '@^(.*)([0-9]{8}_[0-9]{6})\\.' . $ext . '$@' , $file , $matches ) === 1 )
		{
			return \DateTime::createFromFormat( self::BACKUP_DATE_FORMAT , $matches[ 2 ] );
		}
		// @codeCoverageIgnoreStart
		// Cannot happen because of glob but safer
		else
		{
			return null;
		}
		// @codeCoverageIgnoreEnd
	}
}


