<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class Gh29Tests extends TestCase
{
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

		Tools::unlinkGlobFiles( self::LANG_DIR_PATH . '/*/message*.php' );

		self::$langFile = self::LANG_DIR_PATH . '/en/message.php';
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/29
	 */
	public function testObsoleteStringsSHouldReturnInMainArray()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh29/code' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::LANG_DIR_PATH );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$lemmas = require( self::$langFile );
		$this->assertArrayHasKey( 'lemma1' , $lemmas );


		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction'     => true ,
			'--no-backup'          => true ,
			'--verbose'            => true ,
			'--no-date'            => true ,
			'--no-comment'         => true ,
			'--php-file-extension' => 'copy' ,
		) );

		$lemmas = require( self::$langFile );
		$this->assertArrayNotHasKey( 'lemma1' , $lemmas );
		$this->assertArrayHasKey( 'lemma2' , $lemmas );
		$this->assertArrayHasKey( 'lemma1' , $lemmas[ 'LLH:obsolete' ] );


		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
			'--no-comment'     => true ,
		) );

		$lemmas = require( self::$langFile );
		$this->assertArrayHasKey( 'lemma1' , $lemmas );
		$this->assertArrayNotHasKey( 'lemma2' , $lemmas );
		$this->assertArrayHasKey( 'lemma2' , $lemmas[ 'LLH:obsolete' ] );
	}
}
