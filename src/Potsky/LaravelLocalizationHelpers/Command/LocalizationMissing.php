<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Config;
use Illuminate\Config\Repository;
use Potsky\LaravelLocalizationHelpers\Factory\Exception;
use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\Tools;
use Symfony\Component\Console\Input\InputOption;

class LocalizationMissing extends LocalizationAbstract
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'localization:missing';

	/**
	 * Ignore these lang subfolders
	 *
	 * https://laravel.com/docs/4.2/localization
	 * https://laravel.com/docs/5.1/localization
	 *
	 * @var array
	 */
	protected $ignoreVendors = array( 'vendor' , 'packages' );

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Parse all translations in app directory and build all lang files';

	/**
	 * functions and method to catch translations
	 *
	 * @var  array
	 */
	protected $trans_methods = array();

	/**
	 * functions and method to catch translations
	 *
	 * @var  array
	 */
	protected $editor = '';

	/**
	 * Folders to seek for missing translations
	 *
	 * @var  array
	 */
	protected $folders = array();

	/**
	 * Never make lemmas containing these keys obsolete
	 *
	 * @var  array
	 */
	protected $never_obsolete_keys = array();

	/**
	 * Never manage these lang files
	 *
	 * @var  array
	 */
	protected $ignore_lang_files = array();

	/**
	 * The lang folder path where are stored lang files in locale sub-directory
	 *
	 * @var  array
	 */
	protected $lang_folder_path = array();

	/**
	 * The code style list of fixers to apply
	 *
	 * @var  array
	 */
	protected $code_style_fixers = array();

	/**
	 * The code style level to apply
	 *
	 * @var  string
	 */
	protected $code_style_level = null;

	/**
	 * The obsolete lemma array key in which to store obsolete lemma
	 *
	 * @var  string
	 *
	 * @since 2.x.2
	 */
	protected $obsolete_array_key = 'LLH:obsolete';

	/**
	 * The dot notation split regex
	 *
	 * @var  string
	 *
	 * @since 2.x.5
	 */
	protected $dot_notation_split_regex = null;

	/**
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		parent::__construct( $configRepository );

		$this->trans_methods            = config( Localization::PREFIX_LARAVEL_CONFIG . 'trans_methods' );
		$this->folders                  = config( Localization::PREFIX_LARAVEL_CONFIG . 'folders' );
		$this->ignore_lang_files        = config( Localization::PREFIX_LARAVEL_CONFIG . 'ignore_lang_files' );
		$this->lang_folder_path         = config( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' );
		$this->never_obsolete_keys      = config( Localization::PREFIX_LARAVEL_CONFIG . 'never_obsolete_keys' );
		$this->editor                   = config( Localization::PREFIX_LARAVEL_CONFIG . 'editor_command_line' );
		$this->code_style_fixers        = config( Localization::PREFIX_LARAVEL_CONFIG . 'code_style.fixers' );
		$this->code_style_level         = config( Localization::PREFIX_LARAVEL_CONFIG . 'code_style.level' );
		$this->dot_notation_split_regex = config( Localization::PREFIX_LARAVEL_CONFIG . 'dot_notation_split_regex' );

		if ( ! is_string( $this->dot_notation_split_regex ) )
		{
			// fallback to dot if provided regex is not a string
			$this->dot_notation_split_regex = '/\\./';
		}


		// @since 2.x.2
		// Users who have not upgraded their configuration file must have a default
		// but users may want to set it to null to keep the old buggy behaviour
		$this->obsolete_array_key = config( Localization::PREFIX_LARAVEL_CONFIG . 'obsolete_array_key' , $this->obsolete_array_key );
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$folders         = $this->manager->getPath( $this->folders );
		$this->display   = ! $this->option( 'silent' );
		$extension       = $this->option( 'php-file-extension' );
		$obsolete_prefix = ( empty( $this->obsolete_array_key ) ) ? '' : $this->obsolete_array_key . '.';

		//////////////////////////////////////////////////
		// Display where translations are searched in //
		//////////////////////////////////////////////////
		if ( $this->option( 'verbose' ) )
		{
			$this->writeLine( "Lemmas will be searched in the following directories:" );

			foreach ( $folders as $path )
			{
				$this->writeLine( '    <info>' . $path . '</info>' );
			}

			$this->writeLine( '' );
		}

		////////////////////////////////
		// Parse all lemmas from code //
		////////////////////////////////
		$lemmas = $this->manager->extractTranslationsFromFolders( $folders , $this->trans_methods , $extension );

		if ( count( $lemmas ) === 0 )
		{
			$this->writeComment( "No lemma has been found in code." );
			$this->writeLine( "I have searched recursively in PHP files in these directories:" );

			foreach ( $this->manager->getPath( $this->folders ) as $path )
			{
				$this->writeLine( "    " . $path );
			}

			$this->writeLine( "for these functions/methods:" );

			foreach ( $this->trans_methods as $k => $v )
			{
				$this->writeLine( "    " . $k );
			}

			return self::SUCCESS;
		}

		$this->writeLine( ( count( $lemmas ) > 1 ) ? count( $lemmas ) . " lemmas have been found in code" : "1 lemma has been found in code" );

		if ( $this->option( 'verbose' ) )
		{
			foreach ( $lemmas as $key => $value )
			{
				if ( strpos( $key , '.' ) !== false )
				{
					$this->writeLine( '    <info>' . $key . '</info> in file <comment>' . $this->manager->getShortPath( $value ) . '</comment>' );
				}
			}
		}


		/////////////////////////////////////////////
		// Convert dot lemmas to structured lemmas //
		/////////////////////////////////////////////
		if ( $this->option( 'output-flat' ) )
		{
			$lemmas_structured = $this->manager->convertLemmaToFlatArray( $lemmas );

		}
		else
		{
			$lemmas_structured = $this->manager->convertLemmaToStructuredArray( $lemmas , $this->dot_notation_split_regex , null );
		}

		$this->writeLine( '' );


		/////////////////////////////////////
		// Generate lang files :           //
		// - add missing lemmas on top     //
		// - keep already defined lemmas   //
		// - add obsolete lemmas on bottom //
		/////////////////////////////////////
		try
		{
			$dir_lang = $this->manager->getLangPath( $this->lang_folder_path );
		}
		catch ( Exception $e )
		{
			switch ( $e->getCode() )
			{
				//@codeCoverageIgnoreStart
				case Localization::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS:
					$this->writeError( "No lang folder found in these paths:" );
					foreach ( $e->getParameter() as $path )
					{
						$this->writeError( "- " . $path );
					}
					break;
				//@codeCoverageIgnoreEnd

				case Localization::NO_LANG_FOLDER_FOUND_IN_YOUR_CUSTOM_PATH:
					$this->writeError( 'No lang folder found in your custom path: "' . $e->getParameter() . '"' );
					break;
			}

			$this->writeLine( '' );

			return self::ERROR;
		}


		$job           = array();
		$there_are_new = false;

		$this->writeLine( 'Scan files:' );

		foreach ( scandir( $dir_lang ) as $lang )
		{
			if ( in_array( $lang , $this->ignoreVendors ) ) continue;

			if ( Tools::isValidDirectory( $dir_lang , $lang ) )
			{
				foreach ( $lemmas_structured as $family => $array )
				{
					$file_lang_path = $dir_lang . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $family . '.php';

					if ( in_array( $family , $this->ignore_lang_files ) || in_array( $this->manager->getShortPath( $file_lang_path ) , $this->ignore_lang_files ) )
					{
						if ( $this->option( 'verbose' ) )
						{
							$this->writeLine( '' );
							$this->writeInfo( "    ! Skip lang file '$family' !" );
						}
						continue;
					}

					if ( $this->option( 'verbose' ) )
					{
						$this->writeLine( '' );
					}

					$this->writeLine( '    ' . $this->manager->getShortPath( $file_lang_path ) );

					if ( ! $this->option( 'dry-run' ) )
					{

						if ( ! is_writable( dirname( $file_lang_path ) ) )
						{
							// @codeCoverageIgnoreStart
							$this->writeError( "    > Unable to write file in directory " . dirname( $file_lang_path ) );

							return self::ERROR;
							// @codeCoverageIgnoreEnd
						}

						if ( ! file_exists( $file_lang_path ) )
						{
							// @codeCoverageIgnoreStart
							$this->writeInfo( "    > File has been created" );
							// @codeCoverageIgnoreEnd
						}

						if ( ! touch( $file_lang_path ) )
						{
							// @codeCoverageIgnoreStart
							$this->writeError( "    > Unable to touch file $file_lang_path" );

							return self::ERROR;
							// @codeCoverageIgnoreEnd
						}

						if ( ! is_readable( $file_lang_path ) )
						{
							// @codeCoverageIgnoreStart
							$this->writeError( "    > Unable to read file $file_lang_path" );

							return self::ERROR;
							// @codeCoverageIgnoreEnd
						}

						if ( ! is_writable( $file_lang_path ) )
						{
							// @codeCoverageIgnoreStart
							$this->writeError( "    > Unable to write in file $file_lang_path" );

							return self::ERROR;
							// @codeCoverageIgnoreEnd
						}
					}

					/** @noinspection PhpIncludeInspection */
					$a                        = @include( $file_lang_path );
					$old_lemmas_with_obsolete = ( is_array( $a ) ) ? array_dot( $a ) : array();
					$new_lemmas               = array_dot( $array );
					$final_lemmas             = array();
					$display_already_comment  = false;
					$something_to_do          = false;
					$i                        = 0;

					// Remove the obsolete prefix key
					$old_lemmas             = array();
					$obsolete_prefix_length = strlen( $obsolete_prefix );
					foreach ( $old_lemmas_with_obsolete as $key => $value )
					{
						if ( starts_with( $key , $obsolete_prefix ) )
						{
							$key = substr( $key , $obsolete_prefix_length );
							if ( ! isset( $old_lemmas[ $key ] ) )
							{
								$old_lemmas[ $key ] = $value;
							}
						}
						else
						{
							$old_lemmas[ $key ] = $value;
						}
					}

					// Check if keys from new lemmas are not sub-keys from old_lemma (#47)
					// Ignore them if this is the case
					$new_lemmas_clean = $new_lemmas;
					$old_lemmas_clean = $old_lemmas;

					foreach ( $new_lemmas as $new_key => $new_value )
					{
						foreach ( $old_lemmas as $old_key => $old_value )
						{
							if ( starts_with( $old_key , $new_key . '.' ) )
							{
								if ( $this->option( 'verbose' ) )
								{
									$this->writeLine( "            <info>" . $new_key . "</info> seems to be used to access an array and is already defined in lang file as " . $old_key );
									$this->writeLine( "            <info>" . $new_key . "</info> not handled!" );
								}

								unset( $new_lemmas_clean[ $new_key ] );
								unset( $old_lemmas_clean[ $old_key ] );
							}
						}
					}

					$obsolete_lemmas = array_diff_key( $old_lemmas_clean , $new_lemmas_clean );
					$welcome_lemmas  = array_diff_key( $new_lemmas_clean , $old_lemmas_clean );
					$already_lemmas  = array_intersect_key( $old_lemmas_clean , $new_lemmas_clean );

					// disable check for obsolete lemma and consolidate with already_lemmas
					if ( $this->option( 'disable-obsolete-check' ) )
					{
						$already_lemmas  = array_unique( $obsolete_lemmas + $already_lemmas );
						$obsolete_lemmas = array();
					}

					ksort( $obsolete_lemmas );
					ksort( $welcome_lemmas );
					ksort( $already_lemmas );

					//////////////////////////
					// Deal with new lemmas //
					//////////////////////////
					if ( count( $welcome_lemmas ) > 0 )
					{
						$display_already_comment                 = true;
						$something_to_do                         = true;
						$there_are_new                           = true;
						$final_lemmas[ "POTSKY___NEW___POTSKY" ] = "POTSKY___NEW___POTSKY";

						$this->writeInfo( '        ' . ( $c = count( $welcome_lemmas ) ) . ' new string' . Tools::getPlural( $c ) . ' to translate' );

						foreach ( $welcome_lemmas as $key => $value )
						{
							if ( $this->option( 'verbose' ) )
							{
								$this->writeLine( "            <info>" . $key . "</info> in " . $this->manager->getShortPath( $value ) );
							}
							if ( ! $this->option( 'no-comment' ) )
							{
								$final_lemmas[ 'POTSKY___COMMENT___POTSKY' . $i ] = "Defined in file $value";
								$i                                                = $i + 1;
							}

							$key_last_token = preg_split( $this->dot_notation_split_regex , $key );

							if ( $this->option( 'translation' ) )
							{
								$translation = $this->manager->translate( end( $key_last_token ) , $lang );
							}
							else
							{
								$translation = end( $key_last_token );
							}

							if ( strtolower( $this->option( 'new-value' ) ) === 'null' )
							{
								$translation = null;
							}
							else
							{
								$translation = str_replace( '%LEMMA' , $translation , $this->option( 'new-value' ) );
							}

							Tools::arraySet( $final_lemmas , $key , $translation , $this->dot_notation_split_regex );
						}
					}

					///////////////////////////////
					// Deal with existing lemmas //
					///////////////////////////////
					if ( count( $already_lemmas ) > 0 )
					{
						if ( $this->option( 'verbose' ) )
						{
							$this->writeLine( '        ' . ( $c = count( $already_lemmas ) ) . ' already translated string' . Tools::getPlural( $c ) );
						}

						$final_lemmas[ "POTSKY___OLD___POTSKY" ] = "POTSKY___OLD___POTSKY";

						foreach ( $already_lemmas as $key => $value )
						{
							Tools::arraySet( $final_lemmas , $key , $value , $this->dot_notation_split_regex );
						}
					}

					///////////////////////////////
					// Deal with obsolete lemmas //
					///////////////////////////////
					if ( count( $obsolete_lemmas ) > 0 )
					{
						$protected_already_included = false;

						// Remove all dynamic fields
						foreach ( $obsolete_lemmas as $key => $value )
						{
							foreach ( $this->never_obsolete_keys as $remove )
							{
								if ( ( strpos( $key , '.' . $remove . '.' ) !== false ) || starts_with( $key , $remove . '.' ) )
								{
									if ( $this->option( 'verbose' ) )
									{
										$this->writeLine( "        <comment>" . $key . "</comment> is protected as a dynamic lemma" );
									}

									unset( $obsolete_lemmas[ $key ] );

									if ( $protected_already_included === false )
									{
										$final_lemmas[ "POTSKY___PROTECTED___POTSKY" ] = "POTSKY___PROTECTED___POTSKY";
										$protected_already_included                    = true;
									}

									// Given that this lemma is never obsolete, we need to send it back to the final lemma array
									Tools::arraySet( $final_lemmas , $key , $value , $this->dot_notation_split_regex );
								}
							}
						}
					}


					/////////////////////////////////////
					// Fill the final lemmas array now //
					/////////////////////////////////////
					if ( count( $obsolete_lemmas ) > 0 )
					{
						$display_already_comment = true;
						$something_to_do         = true;

						if ( $this->option( 'no-obsolete' ) )
						{
							$this->writeComment( "        " . ( $c = count( $obsolete_lemmas ) ) . ' obsolete string' . Tools::getPlural( $c ) . ' (will be deleted)' );
						}
						else
						{
							$this->writeComment( "        " . ( $c = count( $obsolete_lemmas ) ) . ' obsolete string' . Tools::getPlural( $c ) . ' (can be deleted manually in the generated file)' );

							$final_lemmas[ "POTSKY___OBSOLETE___POTSKY" ] = "POTSKY___OBSOLETE___POTSKY";

							foreach ( $obsolete_lemmas as $key => $value )
							{
								if ( $this->option( 'verbose' ) )
								{
									$this->writeLine( "            <comment>" . $key . "</comment>" );
								}

								Tools::arraySet( $final_lemmas , $obsolete_prefix . $key , $value , $this->dot_notation_split_regex );
							}
						}
					}

					// Flat style
					if ( $this->option( 'output-flat' ) )
					{
						$final_lemmas = array_dot( $final_lemmas );
					}

					if ( ( $something_to_do === true ) || ( $this->option( 'force' ) ) )
					{
						$content = var_export( $final_lemmas , true );
						$content = preg_replace( "@'POTSKY___COMMENT___POTSKY[0-9]*' => '(.*)',@" , '// $1' , $content );
						$content = str_replace(
							array(
								"'POTSKY___NEW___POTSKY' => 'POTSKY___NEW___POTSKY'," ,
								"'POTSKY___OLD___POTSKY' => 'POTSKY___OLD___POTSKY'," ,
								"'POTSKY___PROTECTED___POTSKY' => 'POTSKY___PROTECTED___POTSKY'," ,
								"'POTSKY___OBSOLETE___POTSKY' => 'POTSKY___OBSOLETE___POTSKY'," ,
							) ,
							array(
								'//============================== New strings to translate ==============================//' ,
								( $display_already_comment === true ) ? '//==================================== Translations ====================================//' : '' ,
								'//============================== Dynamic protected strings =============================//' ,
								'//================================== Obsolete strings ==================================//' ,
							) ,
							$content
						);

						$file_content = "<?php\n";

						if ( ! $this->option( 'no-date' ) )
						{
							$a = " Generated via \"php artisan " . $this->argument( 'command' ) . "\" at " . date( "Y/m/d H:i:s" ) . " ";
							$file_content .= "/" . str_repeat( '*' , strlen( $a ) ) . "\n" . $a . "\n" . str_repeat( '*' , strlen( $a ) ) . "/\n";
						}

						$file_content .= "\nreturn " . $content . ";";
						$job[ $file_lang_path ] = $file_content;
					}
					else
					{
						if ( $this->option( 'verbose' ) )
						{
							$this->writeLine( "        > <comment>Nothing to do for this file</comment>" );
						}
					}
				}
			}
		}

		///////////////////////////////////////////
		// Silent mode                           //
		// only return an exit code on new lemma //
		///////////////////////////////////////////
		if ( $this->option( 'silent' ) )
		{
			if ( $there_are_new === true )
			{
				return self::ERROR;
			}
			else
			{
				// @codeCoverageIgnoreStart
				return self::SUCCESS;
				// @codeCoverageIgnoreEnd
			}
		}

		///////////////////////////////////////////
		// Normal mode                           //
		///////////////////////////////////////////
		if ( count( $job ) > 0 )
		{

			if ( $this->option( 'no-interaction' ) )
			{
				$do = true;
			}
			// @codeCoverageIgnoreStart
			else
			{
				$this->writeLine( '' );
				$do = ( $this->ask( 'Do you wish to apply these changes now? [yes|no]' ) === 'yes' );
				$this->writeLine( '' );
			}
			// @codeCoverageIgnoreEnd

			if ( $do === true )
			{

				if ( ! $this->option( 'no-backup' ) )
				{
					$this->writeLine( 'Backup files:' );

					$now = $this->manager->getBackupDate();

					foreach ( $job as $file_lang_path => $file_content )
					{
						$backup_path = $this->manager->getBackupPath( $file_lang_path , $now , $extension );

						if ( ! $this->option( 'dry-run' ) )
						{
							rename( $file_lang_path , $backup_path );
						}

						$this->writeLine( "    <info>" . $this->manager->getShortPath( $file_lang_path ) . "</info> -> <info>" . $this->manager->getShortPath( $backup_path ) . "</info>" );
					}

					$this->writeLine( '' );
				}

				$this->writeLine( 'Save files:' );
				$open_files = '';

				foreach ( $job as $file_lang_path => $file_content )
				{
					if ( ! $this->option( 'dry-run' ) )
					{
						file_put_contents( $file_lang_path , $file_content );
					}

					$this->writeLine( "    <info>" . $this->manager->getShortPath( $file_lang_path ) . "</info>" );

					// Fix code style
					if ( ( ! empty( $this->code_style_level ) ) || ( ! empty( $this->code_style_fixers ) ) )
					{
						try
						{
							$this->manager->fixCodeStyle( $file_lang_path , $this->code_style_fixers , $this->code_style_level );
						}
							// @codeCoverageIgnoreStart
						catch ( Exception $e )
						{
							$this->writeError( "    Cannot fix code style (" . $e->getMessage() . ")" );
						}
						// @codeCoverageIgnoreEnd
					}

					// @codeCoverageIgnoreStart
					if ( $this->option( 'editor' ) )
					{
						$open_files .= ' ' . escapeshellarg( $file_lang_path );
					}
					// @codeCoverageIgnoreEnd

				}

				$this->writeLine( '' );
				$this->writeInfo( 'Process done!' );

				// @codeCoverageIgnoreStart
				if ( $this->option( 'editor' ) )
				{
					exec( $this->editor . $open_files );
				}
				// @codeCoverageIgnoreEnd

				// @codeCoverageIgnoreStart
			}
			else
			{
				$this->writeLine( '' );
				$this->writeComment( 'Process aborted. No file has been changed.' );
			}
		}
		// @codeCoverageIgnoreEnd
		else
		{
			$this->writeLine( '' );
			$this->writeInfo( 'Drink a Piña colada and/or smoke Super Skunk, you have nothing to do!' );
		}
		$this->writeLine( '' );

		return self::SUCCESS;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array( 'dry-run' , 'r' , InputOption::VALUE_NONE , 'Dry run: run process but do not write anything' ) ,
			array( 'editor' , 'e' , InputOption::VALUE_NONE , 'Open files which need to be edited at the end of the process' ) ,
			array( 'force' , 'f' , InputOption::VALUE_NONE , 'Force files to be rewritten even if there is nothing to do' ) ,
			array( 'translation' , 't' , InputOption::VALUE_NONE , 'Try to translate the lemma to the target language' ) ,
			array( 'new-value' , 'l' , InputOption::VALUE_OPTIONAL , 'Value of new found lemmas (use %LEMMA for the lemma value or translation). Set it to null to provide translation fallbacks.' , 'TODO: %LEMMA' ) ,
			array( 'no-backup' , 'b' , InputOption::VALUE_NONE , 'Do not backup lang file (be careful, I am not a good coder)' ) ,
			array( 'no-comment' , 'c' , InputOption::VALUE_NONE , 'Do not add comments in lang files for lemma definition' ) ,
			array( 'no-date' , 'd' , InputOption::VALUE_NONE , 'Do not add the date of execution in the lang files' ) ,
			array( 'no-obsolete' , 'o' , InputOption::VALUE_NONE , 'Do not write obsolete lemma (obsolete lemma will be removed)' ) ,
			array( 'output-flat' , 'w' , InputOption::VALUE_NONE , 'Output arrays are flat (do not use sub-arrays and keep dots in lemma)' ) ,
			array( 'silent' , 's' , InputOption::VALUE_NONE , 'Use this option to only return the exit code (use $? in shell to know whether there are missing lemma or nt)' ) ,
			array( 'php-file-extension' , 'x' , InputOption::VALUE_OPTIONAL , 'PHP file extension' , 'php' ) ,
			array( 'disable-obsolete-check' , 'z' , InputOption::VALUE_NONE , 'Use this option to disable check for obsolete lemmas (obsolete lemma will be kept)' ) ,
		);
	}

}
