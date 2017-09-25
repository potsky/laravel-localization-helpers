	<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh31Tests extends TestCase
{
	private static $langFolder;

	private static $langFile;

	private static $langFileVendor;

	private static $langFileVendor42;

	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		self::$langFolder       = self::MOCK_DIR_PATH . '/gh31/lang';
		self::$langFile         = self::$langFolder . '/en/message.php';
		self::$langFileVendor42 = self::$langFolder . '/packages/message.php';
		self::$langFileVendor   = self::$langFolder . '/vendor/message.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/31
	 */
	public function testVendorIsIgnored()
	{
		@unlink( self::$langFile );
		@unlink( self::$langFileVendor );
		@unlink( self::$langFileVendor42 );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh31/code' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$this->assertFileExists( self::$langFile );
		$this->assertFileNotExists( self::$langFileVendor );
		$this->assertFileNotExists( self::$langFileVendor42 );
	}

}
