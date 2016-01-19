<?php

namespace Potsky\LaravelLocalizationHelpers\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
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
