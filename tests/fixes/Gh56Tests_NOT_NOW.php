<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh56TestsNOTNOW extends TestCase
{
	private static $langFolder;

	private static $langFileEn;
	private static $langFileFr;

	private static $langFileJsonEn;

	private static $langFileJsonFr;

	private static $langFileIncorrectGenuine;


	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		self::$langFolder               = self::MOCK_DIR_PATH . '/gh56/lang';
		self::$langFileEn               = self::$langFolder . '/en/message.php';
		self::$langFileFr               = self::$langFolder . '/fr/message.php';
		self::$langFileJsonEn           = self::$langFolder . '/en.json';
		self::$langFileJsonFr           = self::$langFolder . '/fr.json';
		self::$langFileIncorrectGenuine = self::$langFolder . '/en/message....php';
	}

//
//	/**
//	 * https://github.com/potsky/laravel-localization-helpers/issues/56
//	 */
//	public function testDefaultDotNotation()
//	{
//		@unlink( self::$langFileEn );
//		@unlink( self::$langFileFr );
//		@unlink( self::$langFileJsonEn );
//		@unlink( self::$langFileJsonFr );
//		@rmdir( dirname( self::$langFileFr ) );
//
//		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
//		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh56/code' );
//		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'dot_notation_split_regex' , null );
//		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'json_languages' , array( 'en' , 'fr' ) );
//
//		/** @noinspection PhpVoidFunctionResultUsedInspection */
//		Artisan::call( 'localization:missing' , array(
//			'--no-interaction' => true ,
//			'--no-backup'      => true ,
//			'--verbose'        => true ,
//			'--no-date'        => true ,
//			'--no-comment'     => true ,
//		) );
//
//		$this->assertFileExists( self::$langFileEn );
//		$this->assertFileNotExists( self::$langFileJsonEn );
//		$this->assertFileNotExists( self::$langFileJsonFr );
//
//		$lemmas = require( self::$langFileEn );
//
//		$this->assertArrayHasKey( "message" , $lemmas );
//		$this->assertArrayHasKey( "" , $lemmas[ "message" ][ "" ] );
//	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/56
	 */
	public function testAwesomeDotNotation()
	{
		@unlink( self::$langFileFr );
		@unlink( self::$langFileEn );
		@unlink( self::$langFileJsonEn );
		@unlink( self::$langFileJsonFr );
		@rmdir( dirname( self::$langFileFr ) );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh56/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'dot_notation_split_regex' , '/\\.(?=[^ .!?])/' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'json_languages' , array( 'en' , 'fr' ) );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$this->assertFileNotExists( self::$langFileIncorrectGenuine );
		$this->assertFileExists( self::$langFileEn );
		$this->assertFileExists( self::$langFileJsonEn );
		$this->assertFileExists( self::$langFileJsonFr );

		$lemmas = require( self::$langFileEn );

		$this->assertArrayHasKey( "message..." , $lemmas );
		$this->assertInternalType( 'string' , $lemmas[ 'message...' ] );

		$lemmas = file_get_contents( self::$langFileJsonEn );
		$this->assertJson( $lemmas );
	}


}
