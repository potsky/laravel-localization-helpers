<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Config;
use Illuminate\Config\Repository;
use Potsky\LaravelLocalizationHelpers\Factory\Localization;
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

		$this->folders       = config( Localization::PREFIX_LARAVEL_CONFIG . 'folders' );
		$this->trans_methods = config( Localization::PREFIX_LARAVEL_CONFIG . 'trans_methods' );
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
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
		$files = $this->manager->findLemma( $lemma , $folders , $this->trans_methods , $this->option( 'regex' ) , $this->option( 'short' ) );

		if ( ( is_array( $files ) ) && ( count( $files ) > 0 ) )
		{
			$this->writeLine( 'Lemma <info>' . $lemma . '</info> has been found in:' );
			foreach ( $files as $file )
			{
				$this->writeLine( '    <info>' . $file . '</info>' );
			}

			return self::SUCCESS;
		}
		else
		{
			return self::ERROR;
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
