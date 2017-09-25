<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Config;
use Illuminate\Config\Repository;
use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Symfony\Component\Console\Input\InputOption;

class LocalizationClear extends LocalizationAbstract
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'localization:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove lang backup files';

	/**
	 * The lang folder path where are stored lang files in locale sub-directory
	 *
	 * @var  array
	 */
	protected $lang_folder_path = array();

	/**
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		$this->lang_folder_path = config( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' );

		parent::__construct( $configRepository );
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$days = (int)$this->option( 'days' );

		if ( $days < 0 )
		{
			$this->writeError( "days option cannot be negative" );

			return self::ERROR;
		}

		$success = $this->manager->deleteBackupFiles( $this->lang_folder_path , $days , $this->option( 'dry-run' ) );

		return ( $success === true ) ? self::SUCCESS : self::ERROR;
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
			array( 'days' , 'd' , InputOption::VALUE_REQUIRED , 'Remove backups older than this count of days' , 0 ) ,
		);
	}

}
