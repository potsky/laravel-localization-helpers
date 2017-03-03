<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh52Tests extends TestCase
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

		self::$langFolder = self::MOCK_DIR_PATH . '/gh52/lang';
		self::$langFile   = self::$langFolder . '/en/message.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/52
	 */
	public function testNoEscapeCharNoRegex()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh52/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'escape_char' , null );
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

		$this->assertArrayHasKey( " Second sentence" , $lemmas[ "@First sentence" ] );
		$this->assertArrayHasKey( "" , $lemmas[ "@To be continued" ][ "" ][ "" ] );
		$this->assertArrayHasKey( "" , $lemmas[ "First sentence" ][ "@Second sentence" ] );
	}



	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/52
	 */
	public function testOutputFlatNoMatterWhatAreEscapeCharAndRegex()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh52/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'escape_char' , '@' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'dot_notation_split_regex' , '/\\.(?=[^ .!?])/' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
			'--output-flat'    => true ,
		) );

		$lemmas = require( self::$langFile );

		$this->assertInternalType( "string" , $lemmas[ "@First sentence. Second sentence." ] );
  		$this->assertInternalType( "string" , $lemmas[ "@To be continued..." ] );
  		$this->assertInternalType( "string" , $lemmas[ "First sentence.@Second sentence." ] );
	}


	public function testEscapeChar()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh52/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'escape_char' , '@' );
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
	}

	
	public function testRegex()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh52/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'escape_char' , null );
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
	}


	public function testEscapeAndRegex()
	{
		@unlink( self::$langFile );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh52/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'escape_char' , '@' );
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
	}

}
