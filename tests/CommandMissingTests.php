<?php

class CommandMissingTests extends TestCase
{
	/**
	 * Setup the test environment.
	 *
	 * - Remove all previous lang files before each test
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		\Potsky\LaravelLocalizationHelpers\Factory\Tools::unlinkGlobFiles( self::LANG_DIR_PATH . '/*/message*.php' );

		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , self::LANG_DIR_PATH );
		Config::set( 'laravel-localization-helpers::config.folders' , self::MOCK_DIR_PATH );
	}

	/**
	 * - lang files have been created
	 * - lemma without a family is rejected
	 * - the default lang file array is structured
	 * - the default translation is prefixed by TO DO
	 */
	public function testLangFileDoesNotExist()
	{
		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );
		$result = $output->fetch();

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'File has been created' , $result );
		$this->assertContains( 'OUPS' , $result );

		$lemmas = include( self::LANG_DIR_PATH . '/fr/message.php' );
		$this->assertEquals( 'TODO: lemma.child' , $lemmas[ 'lemma' ][ 'child' ] );
	}

	/**
	 * - Set a non existing lang folder
	 */
	public function testLangFolderDoesNotExist()
	{
		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , self::LANG_DIR_PATH . 'doesnotexist' );

		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );

		$this->assertEquals( 1 , $return );
		$this->assertContains( 'No lang folder found in your custom path:' , $output->fetch() );
	}

	/**
	 * - Default lang folders are used when custom land folder path as not been set by user
	 */
	public function testDefaultLangFolderDoesNotExist()
	{
		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , null );

		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );

		$this->assertEquals( 1 , $return );
		$this->assertContains( 'No lang folder found in these paths:' , $output->fetch() );
	}

	/**
	 * - Default lang folders are used when custom land folder path as not been set by user
	 */
	public function testDefaultLangFolderExists()
	{
		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , null );

		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		@mkdir( self::ORCHESTRA_LANG_DIR_PATH );
		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );
		rmdir( self::ORCHESTRA_LANG_DIR_PATH );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'Drink a Piña colada and/or smoke Super Skunk, you have nothing to do!' , $output->fetch() );

	}

	/**
	 * - create dumb lang files to verify backups are done
	 */
	public function testLangFileExistsWithBackup()
	{
		touch( self::LANG_DIR_PATH . '/en/message.php' );
		touch( self::LANG_DIR_PATH . '/fr/message.php' );

		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'Backup files' , $output->fetch() );
	}

	/**
	 * - the default lang file array is structured
	 * - the new-value option works
	 */
	public function testFlatOutput()
	{
		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--output-flat'    => true ,
			'--new-value'      => '%LEMMA POTSKY' ,
		) , $output );

		$this->assertEquals( 0 , $return );

		$lemmas = include( self::LANG_DIR_PATH . '/fr/message.php' );
		$this->assertEquals( 'lemma.child POTSKY' , $lemmas[ 'lemma.child' ] );
	}


}
