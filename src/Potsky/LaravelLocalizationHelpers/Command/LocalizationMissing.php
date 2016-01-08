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
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		parent::__construct( $configRepository );

		$this->trans_methods       = Config::get( 'laravel-localization-helpers::config.trans_methods' );
		$this->folders             = Config::get( 'laravel-localization-helpers::config.folders' );
		$this->ignore_lang_files   = Config::get( 'laravel-localization-helpers::config.ignore_lang_files' );
		$this->lang_folder_path    = Config::get( 'laravel-localization-helpers::config.lang_folder_path' );
		$this->never_obsolete_keys = Config::get( 'laravel-localization-helpers::config.never_obsolete_keys' );
		$this->editor              = Config::get( 'laravel-localization-helpers::config.editor_command_line' );
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$folders       = $this->manager->getPath( $this->folders );
		$this->display = ! $this->option( 'silent' );

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
		$lemmas = $this->manager->extractTranslationsFromFolders( $folders , $this->trans_methods , $this->option( 'php-file-extension' ) );

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
			$lemmas_structured = $this->manager->convertLemmaToStructuredArray( $lemmas );
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
				case Localization::NO_LANG_FOLDER_FOUND_IN_THESE_PATHS:
					$this->writeError( "No lang folder found in these paths:" );
					foreach ( $e->getParameter() as $path )
					{
						$this->writeError( "- " . $path );
					}
					break;

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
			if ( Tools::isValidDirectory( $dir_lang , $lang ) )
			{
				foreach ( $lemmas_structured as $family => $array )
				{
					if ( in_array( $family , $this->ignore_lang_files ) )
					{
						if ( $this->option( 'verbose' ) )
						{
							$this->writeLine( '' );
							$this->writeInfo( "    ! Skip lang file '$family' !" );
						}
						continue;
					}

					$file_lang_path = $dir_lang . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $family . '.php';

					if ( $this->option( 'verbose' ) )
					{
						$this->writeLine( '' );
					}
					$this->writeLine( '    ' . $this->manager->getShortPath( $file_lang_path ) );

					if ( ! is_writable( dirname( $file_lang_path ) ) )
					{
						$this->writeError( "    > Unable to write file in directory " . dirname( $file_lang_path ) );

						return self::ERROR;
					}

					if ( ! file_exists( $file_lang_path ) )
					{
						$this->writeInfo( "    > File has been created" );
					}

					if ( ! touch( $file_lang_path ) )
					{
						$this->writeError( "    > Unable to touch file $file_lang_path" );

						return self::ERROR;
					}

					if ( ! is_readable( $file_lang_path ) )
					{
						$this->writeError( "    > Unable to read file $file_lang_path" );

						return self::ERROR;
					}

					if ( ! is_writable( $file_lang_path ) )
					{
						$this->writeError( "    > Unable to write in file $file_lang_path" );

						return self::ERROR;
					}

					/** @noinspection PhpIncludeInspection */
					$a                       = include( $file_lang_path );
					$old_lemmas              = ( is_array( $a ) ) ? array_dot( $a ) : array();
					$new_lemmas              = array_dot( $array );
					$final_lemmas            = array();
					$display_already_comment = false;
					$something_to_do         = false;
					$i                       = 0;
					$obsolete_lemmas         = array_diff_key( $old_lemmas , $new_lemmas );
					$welcome_lemmas          = array_diff_key( $new_lemmas , $old_lemmas );
					$already_lemmas          = array_intersect_key( $old_lemmas , $new_lemmas );
					ksort( $obsolete_lemmas );
					ksort( $welcome_lemmas );
					ksort( $already_lemmas );

					//////////////////////////
					// Deal with new lemmas //
					//////////////////////////
					if ( count( $welcome_lemmas ) > 0 )
					{
						$display_already_comment = true;
						$something_to_do         = true;
						$there_are_new           = true;
						$this->writeInfo( "        " . count( $welcome_lemmas ) . " new strings to translate" );
						$final_lemmas[ "POTSKY___NEW___POTSKY" ] = "POTSKY___NEW___POTSKY";

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

							array_set( $final_lemmas , $key , str_replace( '%LEMMA' , $key , $this->option( 'new-value' ) ) );
						}
					}

					///////////////////////////////
					// Deal with existing lemmas //
					///////////////////////////////
					if ( count( $already_lemmas ) > 0 )
					{
						if ( $this->option( 'verbose' ) )
						{
							$this->writeLine( "        " . count( $already_lemmas ) . " already translated strings" );
						}

						$final_lemmas[ "POTSKY___OLD___POTSKY" ] = "POTSKY___OLD___POTSKY";

						foreach ( $already_lemmas as $key => $value )
						{
							array_set( $final_lemmas , $key , $value );
						}
					}

					///////////////////////////////
					// Deal with obsolete lemmas //
					///////////////////////////////
					if ( count( $obsolete_lemmas ) > 0 )
					{
						// Remove all dynamic fields
						foreach ( $obsolete_lemmas as $key => $value )
						{
							foreach ( $this->never_obsolete_keys as $remove )
							{
								if ( ( strpos( $key , '.' . $remove . '.' ) !== false ) || starts_with( $key , $remove . '.' ) )
								{
									unset( $obsolete_lemmas[ $key ] );
								}
							}
						}
					}

					if ( count( $obsolete_lemmas ) > 0 )
					{
						$display_already_comment = true;
						$something_to_do         = true;
						$this->writeComment( $this->option( 'no-obsolete' )
							? "        " . count( $obsolete_lemmas ) . " obsolete strings (will be deleted)"
							: "        " . count( $obsolete_lemmas ) . " obsolete strings (can be deleted manually in the generated file)"
						);
						$final_lemmas[ "POTSKY___OBSOLETE___POTSKY" ] = "POTSKY___OBSOLETE___POTSKY";

						foreach ( $obsolete_lemmas as $key => $value )
						{
							if ( $this->option( 'verbose' ) )
							{
								$this->writeLine( "            <comment>" . $key . "</comment>" );
							}
							if ( ! $this->option( 'no-obsolete' ) )
							{
								array_set( $final_lemmas , $key , $value );
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
								"'POTSKY___OBSOLETE___POTSKY' => 'POTSKY___OBSOLETE___POTSKY'," ,
							) ,
							array(
								'//============================== New strings to translate ==============================//' ,
								( $display_already_comment === true ) ? '//==================================== Translations ====================================//' : '' ,
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
				return self::SUCCESS;
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
			else
			{
				$this->writeLine( '' );
				$do = ( $this->ask( 'Do you wish to apply these changes now? [yes|no]' ) === 'yes' );
				$this->writeLine( '' );
			}

			if ( $do === true )
			{

				if ( ! $this->option( 'no-backup' ) )
				{
					$this->writeLine( 'Backup files:' );
					foreach ( $job as $file_lang_path => $file_content )
					{
						$backup_path = preg_replace( '/\..+$/' , '.' . date( "Ymd_His" ) . '.php' , $file_lang_path );
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
					$this->writeLine( "    <info>" . $this->manager->getShortPath( $file_lang_path ) );
					if ( $this->option( 'editor' ) )
					{
						$open_files .= ' ' . escapeshellarg( $file_lang_path );
					}
				}
				$this->writeLine( '' );

				$this->writeInfo( 'Process done!' );

				if ( $this->option( 'editor' ) )
				{
					exec( $this->editor . $open_files );
				}

			}
			else
			{
				$this->writeLine( '' );
				$this->writeComment( 'Process aborted. No file has been changed.' );
			}
		}
		else
		{
			if ( $this->option( 'silent' ) )
			{
				return self::SUCCESS;
			}

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
			array( 'new-value' , 'l' , InputOption::VALUE_OPTIONAL , 'Value of new found lemmas (use %LEMMA for the lemma value)' , 'TODO: %LEMMA' ) ,
			array( 'no-backup' , 'b' , InputOption::VALUE_NONE , 'Do not backup lang file (be careful, I am not a good coder)' ) ,
			array( 'no-comment' , 'c' , InputOption::VALUE_NONE , 'Do not add comments in lang files for lemma definition' ) ,
			array( 'no-date' , 'd' , InputOption::VALUE_NONE , 'Do not add the date of execution in the lang files' ) ,
			array( 'no-obsolete' , 'o' , InputOption::VALUE_NONE , 'Do not write obsolete lemma' ) ,
			array( 'output-flat' , 'of' , InputOption::VALUE_NONE , 'Output arrays are flat (do not use sub-arrays and keep dots in lemma)' ) ,
			array( 'silent' , 's' , InputOption::VALUE_NONE , 'Use this option to only return the exit code (use $? in shell to know whether there are missing lemma or nt)' ) ,
			array( 'php-file-extension' , 'ex' , InputOption::VALUE_OPTIONAL , 'PHP file extension' , 'php' ) ,
		);
	}

}
