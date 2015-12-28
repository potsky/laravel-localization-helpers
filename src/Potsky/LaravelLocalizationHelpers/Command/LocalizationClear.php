<?php

namespace Potsky\LaravelLocalizationHelpers\Command;

use Illuminate\Config\Repository;
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
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		parent::__construct( $configRepository );
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->writeInfo( (int)$this->option('days') );
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
			array( 'days' , 'd' , InputOption::VALUE_REQUIRED , 'Remove backups older than this count of days' )
		);
	}

}
