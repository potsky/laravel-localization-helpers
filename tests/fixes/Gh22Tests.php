<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;

class Gh22Tests extends TestCase
{
	private static $langFolder;

	private static $langFile;

	private static $defaultLangContent = "<?php
return array(
	'my dog is rich' => 'My dog is rich' ,
	'section'        => array(
		1 => array(
			'name' => 'Niania',
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

		self::$langFolder = self::MOCK_DIR_PATH . '/gh22/lang';
		self::$langFile   = self::$langFolder . '/en/message.php';

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::$langFolder );

		// Set content in lang file
		File::put( self::$langFile , self::$defaultLangContent );
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/22
	 */
	public function CACAtestObsoleteKeyIsNotRemoved()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh22/phase1' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
		) );

		$this->assertContains( '1 obsolete strings' , Artisan::output() );

		$this->assertArrayHasKey( 'section' , require( self::$langFile ) );
	}


	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/22
	 */
	public function CACAtestObsoleteKeyIsRemovedWhenSettingOption()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh22/phase1' );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--no-obsolete'    => true ,
		) );

		$this->assertContains( '1 obsolete strings' , Artisan::output() );

		$this->assertArrayNotHasKey( 'section' , require( self::$langFile ) );
	}

	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/22
	 */
	public function CACAtestDynamicFieldShouldNotBeObsoleteWhenNotAddingANewLemma()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh22/phase1' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'never_obsolete_keys' , array( 'section' ) );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'      => true ,
		) );

		$this->assertEquals( self::$defaultLangContent , File::get( self::$langFile ) );
	}

	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/22
	 */
	public function testDynamicFieldShouldNotBeObsoleteWhenAddingANewLemma()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH . '/gh22/phase2' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'never_obsolete_keys' , array( 'section' ) );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--no-backup'      => true ,
			'--verbose'      => true ,
		) );

		$this->assertArrayHasKey( 'section' , require( self::$langFile ) );
	}

}
