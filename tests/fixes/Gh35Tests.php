<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh35Tests extends TestCase
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

		self::$langFolder = self::MOCK_DIR_PATH . '/gh35/lang';
		self::$langFile   = self::$langFolder . '/en/message.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/35
	 */
	public function testMultilineTransShouldBeCatched()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh35/code' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$lemmas = require( self::$langFile );

		$this->assertArrayHasKey( 'multiline1' , $lemmas );
		$this->assertArrayHasKey( 'multiline2' , $lemmas );
		$this->assertArrayHasKey( 'multiline3' , $lemmas );
		$this->assertArrayHasKey( 'multiline4' , $lemmas );
		$this->assertArrayHasKey( 'multiline5' , $lemmas );
		$this->assertArrayHasKey( 'multiline6' , $lemmas );
		$this->assertArrayHasKey( 'multiline7' , $lemmas );
		$this->assertArrayHasKey( 'multiline8' , $lemmas );
	}

}
