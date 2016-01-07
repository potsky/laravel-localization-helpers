<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Config;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class LocalizationFind extends LocalizationAbstract
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'localization:find';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Display all files where the argument is used as a lemma';

	/**
	 * functions and method to catch translations
	 *
	 * @var  array
	 */
	protected $trans_methods = array();

	/**
	 * Folders to seek for missing translations
	 *
	 * @var  array
	 */
	protected $folders = array();

	/**
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		parent::__construct( $configRepository );

		$this->folders       = Config::get( 'laravel-localization-helpers::config.folders' );
		$this->trans_methods = Config::get( 'laravel-localization-helpers::config.trans_methods' );
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

		$lemma   = $this->argument( 'lemma' );
		$folders = $this->manager->getPath( $this->folders );

		//////////////////////////////////////////////////
		// Display where translatations are searched in //
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
		$files = array();

		foreach ( $folders as $path )
		{
			foreach ( $this->manager->getFilesWithExtension( $path ) as $php_file_path => $dumb )
			{
				foreach ( $this->manager->extractTranslationFromPhpFile( $php_file_path , $this->trans_methods ) as $k => $v )
				{
					$real_value = eval( "return $k;" );
					$found      = false;

					if ( $this->option( 'regex' ) )
					{
						try
						{
							$r = preg_match( $lemma , $real_value );
						}
						catch ( \Exception $e )
						{
							$this->writeLine( "<error>The argument is not a valid regular expression:</error>" . str_replace( 'preg_match():' , '' , $e->getMessage() ) );
							die();
						}
						if ( $r === 1 )
						{
							$found = true;
						}
						else if ( $r === false )
						{
							$this->writeError( "The argument is not a valid regular expression" );
							die();
						}
					}
					else
					{
						if ( strpos( $real_value , $lemma ) )
						{
							$found = true;
						}
					}


					if ( $found === true )
					{
						if ( $this->option( 'short' ) )
						{
							$php_file_path = $this->manager->getShortPath( $php_file_path );
						}
						$files[] = $php_file_path;
						break;
					}
				}
			}
		}

		if ( count( $files ) > 0 )
		{
			$this->writeLine( 'Lemma <info>' . $lemma . '</info> has been found in:' );
			foreach ( $files as $file )
			{
				$this->writeLine( '    <info>' . $file . '</info>' );
			}
		}
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array( 'lemma' , InputArgument::REQUIRED , 'Lemma' ) ,
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array( 'regex' , 'r' , InputOption::VALUE_NONE , 'Argument is a regular expression' ) ,
			array( 'short' , 's' , InputOption::VALUE_NONE , 'Short path relative to the laravel project' ) ,
		);
	}

}
