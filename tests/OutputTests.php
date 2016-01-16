<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;
use Potsky\LaravelLocalizationHelpers\Factory\Tools;
use Symfony\Component\Console\Output\BufferedOutput;

class OutputTests extends TestCase
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

		Tools::unlinkGlobFiles( self::LANG_DIR_PATH . '/*/message*.php' );

		Config::set( 'laravel-localization-helpers::config.lang_folder_path' , self::LANG_DIR_PATH );
		Config::set( 'laravel-localization-helpers::config.folders' , self::MOCK_DIR_PATH );

		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--output-flat'    => true ,
			'--new-value'      => '%LEMMA POTSKY' ,
		) , $output );
	}


	/**
	 *
	 */
	public function test()
	{
		$messageBag = new MessageBag();
		$manager    = new Localization( $messageBag );

		/** @noinspection PhpIncludeInspection */
		$this->assertContains( 'array (' , file_get_contents( self::LANG_DIR_PATH . '/fr/message.php' ) );
		$this->assertNotContains( '[' , file_get_contents( self::LANG_DIR_PATH . '/fr/message.php' ) );

		$this->assertContains( 'Fixed all files in' , $manager->fixCodeStyle(
			self::LANG_DIR_PATH . '/fr/message.php' ,
			array( 'align_double_arrow' , 'short_array_syntax' )
		) );

		/** @noinspection PhpIncludeInspection */
		$this->assertContains( '[' , file_get_contents( self::LANG_DIR_PATH . '/fr/message.php' ) );
		$this->assertNotContains( 'array (' , file_get_contents( self::LANG_DIR_PATH . '/fr/message.php' ) );
	}

}
