<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh59Tests extends TestCase
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

		self::$langFolder = self::MOCK_DIR_PATH . '/gh59/lang';
		self::$langFile   = self::$langFolder . '/en/message.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/59
	 */
	public function testDefaultDotNotation()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh59/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'dot_notation_split_regex' , null );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$lemmas = require( self::$langFile );

		$this->assertArrayHasKey( "hello" , $lemmas );
		$this->assertArrayHasKey( "" , $lemmas[ "hello" ][ "" ][ "" ] );

		$this->assertArrayHasKey( "Hello" , $lemmas );
		$this->assertArrayHasKey( " How are you?" , $lemmas[ "Hello" ] );
		$this->assertArrayHasKey( "How are you?" , $lemmas[ "Hello" ] );
	}



	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/59
	 */
	public function testAwesomeDotNotation()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh59/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'dot_notation_split_regex' , '/\\.(?=[^ .!?])/' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$lemmas = require( self::$langFile );

		$this->assertArrayHasKey( "Hello. How are you?" , $lemmas );
		$this->assertInternalType( 'string' , $lemmas[ 'Hello. How are you?' ] );

		$this->assertArrayHasKey( "hello..." , $lemmas );
		$this->assertInternalType( 'string' , $lemmas[ 'hello...' ] );

		$this->assertArrayHasKey( "Hello" , $lemmas );
		$this->assertArrayHasKey( "How are you?" , $lemmas[ "Hello" ] );
	}



}
