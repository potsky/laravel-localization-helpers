<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;

class CommandClearTests extends TestCase
{
	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Create 10 backup files, one per day
	 */
	public function setUp()
	{
		parent::setUp();

		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , self::LANG_DIR_PATH );
		Config::set( 'laravel-localization-helpers::config.folders' , self::MOCK_DIR_PATH );

		\Potsky\LaravelLocalizationHelpers\Factory\Tools::unlinkGlobFiles( self::LANG_DIR_PATH . '/*/message*.php' );

		$manager = new Localization( new MessageBag() );

		for ( $i = 0 ; $i < 10 ; $i++ )
		{
			$time = $manager->getBackupDate( $i );

			touch( self::LANG_DIR_PATH . '/en/message' . $time . '.php' );
			touch( self::LANG_DIR_PATH . '/fr/message' . $time . '.php' );
		}
	}

	/**
	 * - lang files have been created
	 * - lemma without a family is rejected
	 * - the default lang file array is structured
	 * - the default translation is prefixed by TO DO
	 */
	public function testCleanAll()
	{
		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array() , $output );
		$result = $output->fetch();

		$this->assertEquals( 0 , $return );
		$this->assertCount( 0 , glob( self::LANG_DIR_PATH . '/*/message*.php' ) );
		//$this->assertContains( 'File has been created' , $result );

	}
}
