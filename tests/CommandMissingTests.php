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
	 *
	 */
	public function testCallCommandLangFileDoesNotExist()
	{
		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'File has been created' , $output->fetch() );
	}

	/**
	 *
	 */
	public function testCallCommandLangFileExistsWithBackup()
	{
		touch( self::LANG_DIR_PATH . '/en/message.php' );
		touch( self::LANG_DIR_PATH . '/fr/message.php' );

		$output = new \Symfony\Component\Console\Output\BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'Backup files' , $output->fetch() );
	}

}
