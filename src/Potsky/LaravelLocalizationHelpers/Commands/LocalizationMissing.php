<?php

namespace Potsky\LaravelLocalizationHelpers\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
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
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 *
	 * @return void
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
		$this->writeError( 'Version 1.x of this package is deprecated and no more works.');
		$this->writeLine( 'Go to <info>https://github.com/potsky/laravel-localization-helpers</info> and choose the correct package version according to your laravel version');
		return 1;
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
			array( 'dry-run' , 'r' , InputOption::VALUE_NONE , 'Dry run : run process but do not write anything' ) ,
			array( 'editor' , 'e' , InputOption::VALUE_NONE , 'Open files which need to be edited at the end of the process' ) ,
			array( 'force' , 'f' , InputOption::VALUE_NONE , 'Force file rewrite even if there is nothing to do' ) ,
			array( 'new-value' , 'l' , InputOption::VALUE_OPTIONAL , 'Value of new found lemmas (use %LEMMA for the lemma value)' , '%LEMMA' ) ,
			array( 'no-backup' , 'b' , InputOption::VALUE_NONE , 'Do not backup lang file (be careful, I am not a good coder)' ) ,
			array( 'no-comment' , 'c' , InputOption::VALUE_NONE , 'Do not add comments in lang files for lemma definition' ) ,
			array( 'no-date' , 'd' , InputOption::VALUE_NONE , 'Do not add the date of execution in the lang files' ) ,
			array( 'no-obsolete' , 'o' , InputOption::VALUE_NONE , 'Do not write obsolete lemma' ) ,
			array( 'silent' , 's' , InputOption::VALUE_NONE , 'Use this option to only return the exit code (use $? in shell to know whether there are missing lemma)' ) ,
		);
	}

}
