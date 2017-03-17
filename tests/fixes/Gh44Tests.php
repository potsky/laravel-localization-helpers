<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh44Tests extends TestCase
{
	private static $langFolder;

	private static $langValidationEnFile;
	private static $langValidationFrFile;
	private static $langMessageEnFile;
	private static $langMessageFrFile;
	private static $langPotskyEnFile;
	private static $langPotskyFrFile;

	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		self::$langFolder           = self::MOCK_DIR_PATH . '/gh44/lang';
		self::$langValidationEnFile = self::$langFolder . '/en/validation.php';
		self::$langValidationFrFile = self::$langFolder . '/fr/validation.php';
		self::$langMessageEnFile    = self::$langFolder . '/en/message.php';
		self::$langMessageFrFile    = self::$langFolder . '/fr/message.php';
		self::$langPotskyEnFile     = self::$langFolder . '/en/potsky.php';
		self::$langPotskyFrFile     = self::$langFolder . '/fr/potsky.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/44
	 */
	public function testSpecificFilePathInIgnoreConfiguration()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh44/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'ignore_lang_files' , array( 'validation' , 'tests/mock/gh44/lang/fr/potsky.php' ) );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$this->assertFileNotExists( self::$langValidationEnFile );
		$this->assertFileNotExists( self::$langValidationFrFile );
		$this->assertFileNotExists( self::$langPotskyFrFile );
		$this->assertFileExists( self::$langPotskyEnFile );
		$this->assertFileExists( self::$langMessageEnFile );
		$this->assertFileExists( self::$langMessageFrFile );
	}


}
