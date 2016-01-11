<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;
use Symfony\Component\Console\Output\BufferedOutput;

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
	 * All files should be deleted
	 */
	public function testCleanAll()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array() , $output );

		$this->assertEquals( 0 , $return );
		$this->assertCount( 0 , glob( self::LANG_DIR_PATH . '/*/message*.php' ) );
	}

	/**
	 * Nothing should be deleted
	 */
	public function testClean30Days()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array( '--days' => 30 ) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertCount( 20 , glob( self::LANG_DIR_PATH . '/*/message*.php' ) );
	}

	/**
	 * Only 3*2 files should remain
	 */
	public function testClean3Days()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array( '--days' => 3 ) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertCount( 6 , glob( self::LANG_DIR_PATH . '/*/message*.php' ) );
	}

	/**
	 * Error when days is negative
	 */
	public function testErrorDaysNegative()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array( '--days' => -3 ) , $output );
		$this->assertEquals( 1 , $return );

		$manager = new Localization( new MessageBag() );
		$this->assertFalse( $manager->deleteBackupFiles( '' , -3 , true ) );
	}

	/**
	 * Nothing should be deleted
	 */
	public function testDryRun()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array( '--dry-run' => true ) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertCount( 20 , glob( self::LANG_DIR_PATH . '/*/message*.php' ) );
	}


	/**
	 * - Set a non existing lang folder
	 */
	public function testLangFolderDoesNotExist()
	{
		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , self::LANG_DIR_PATH . 'doesnotexist' );

		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array( '--dry-run' => true ) , $output );

		$this->assertEquals( 1 , $return );
		$this->assertContains( 'No lang folder found in your custom path:' , $output->fetch() );
	}

	/**
	 * - Default lang folders are used when custom land folder path as not been set by user
	 */
	public function testDefaultLangFolderDoesNotExist()
	{
		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , null );

		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:clear' , array( '--dry-run' => true ) , $output );

		$this->assertEquals( 1 , $return );
		$this->assertContains( 'No lang folder found in these paths:' , $output->fetch() );
	}

}
