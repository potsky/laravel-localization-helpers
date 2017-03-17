<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh53Tests extends TestCase
{
	private static $langFolder;

	private static $langFile;

	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		self::$langFolder = self::MOCK_DIR_PATH . '/gh53/lang';
		self::$langFile   = self::$langFolder . '/en/message.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/53
	 */
	public function testOddDynamicFieldsShouldNotGenerateErrors()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh53/code' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
			'--dry-run'        => true ,
		) );

		$this->assertContains( 'Unable to understand string' , Artisan::output() );

	}


}
