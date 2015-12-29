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
		$base = ( Tools::getLaravelMajorVersion() >= 5 ) ? 'laravel-localization-helpers.' : 'laravel-localization-helpers::config.';

		$this->trans_methods       = Config::get( $base . 'trans_methods' );
		$this->folders             = Config::get( $base . 'folders' );
		$this->ignore_lang_files   = Config::get( $base . 'ignore_lang_files' );
		$this->lang_folder_path    = Config::get( $base . 'lang_folder_path' );
		$this->never_obsolete_keys = Config::get( $base . 'never_obsolete_keys' );
		$this->editor              = Config::get( $base . 'editor_command_line' );
	}

	public function test()
	{
		return true;
	}
}