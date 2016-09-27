<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh21Tests extends TestCase
{
	private static $langFolder;

	private static $langFile;

	private static $defaultLangContent = "<?php
return array(
	'section'        => array(
		1 => array(
			'name' => 'First lady',
		),
		2 => array(
			'name' => 'Second to die',
		),
	),
);";

	private static $defaultLangWithObsoleteContent = "<?php
return array (
	'section' => array (
		1 => array (
			'name' => 'First lady',
		),
	),
	'LLH:obsolete' => array (
		'section' => array (
			2 => array (
				'name' => 'Second to die',
			),
		),
	),
);";



	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		self::$langFolder = self::MOCK_DIR_PATH . '/gh21/lang';
		self::$langFile   = self::$langFolder . '/en/message.php';

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );

		// Set content in lang file
		File::put( self::$langFile , self::$defaultLangContent );
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/21
	 */
	public function testObsoleteSubKeyRemoved()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh21/code' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'        => true ,
			'--no-date'        => true ,
		) );

		$this->assertContains( '1 obsolete string' , Artisan::output() );

		$this->assertArrayHasKey( 'LLH:obsolete' ,  require( self::$langFile ) );
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/21
	 */
	public function testObsoleteAreKept()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh21/code' );

		// Set content in lang file with obsolete lemma
		File::put( self::$langFile , self::$defaultLangWithObsoleteContent );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--no-date'        => true ,
		) );

		$this->assertContains( '1 obsolete string' , Artisan::output() );

		$lemmas = require( self::$langFile );

		$this->assertArrayHasKey( 'LLH:obsolete' ,  $lemmas );
		$this->assertArrayNotHasKey( 'LLH:obsolete' ,  $lemmas['LLH:obsolete'] );
	}

}
