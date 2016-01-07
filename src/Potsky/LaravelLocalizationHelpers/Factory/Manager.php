<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

use Config;
use Illuminate\Config\Repository;

class Manager
{
	/**
	 * Create a new command instance.
	 *
	 * @param \Illuminate\Config\Repository $configRepository
	 */
	public function __construct( Repository $configRepository )
	{
		$this->trans_methods       = Config::get( 'laravel-localization-helpers.trans_methods' );
		$this->folders             = Config::get( 'laravel-localization-helpers.folders' );
		$this->ignore_lang_files   = Config::get( 'laravel-localization-helpers.ignore_lang_files' );
		$this->lang_folder_path    = Config::get( 'laravel-localization-helpers.lang_folder_path' );
		$this->never_obsolete_keys = Config::get( 'laravel-localization-helpers.never_obsolete_keys' );
		$this->editor              = Config::get( 'laravel-localization-helpers.editor_command_line' );
	}

	public function test()
	{
		return true;
	}
}