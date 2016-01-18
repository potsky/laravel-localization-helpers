<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\Tools;
use Symfony\Component\Console\Output\BufferedOutput;

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

		Tools::unlinkGlobFiles( self::LANG_DIR_PATH . '/*/message*.php' );

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::LANG_DIR_PATH );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'code_style.fixers' , array( 'align_double_arrow' , 'short_array_syntax' ) );

		// Remove all saved access token for translation API
		$translator = new \MicrosoftTranslator\Client( array(
			'api_client_id'     => true ,
			'api_client_secret' => true ,
		) );
		$translator->getAuth()->getGuard()->deleteAllAccessTokens();
	}

	/**
	 * - lang files have been created
	 * - lemma without a family is rejected
	 * - the default lang file array is structured
	 * - the default translation is prefixed by TO DO
	 */
	public function testLangFileDoesNotExist()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array( '--no-interaction' => true ) , $output );
		$result = $output->fetch();

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'File has been created' , $result );
		$this->assertContains( 'OUPS' , $result );

		/** @noinspection PhpIncludeInspection */
		$lemmas = include( self::LANG_DIR_PATH . '/fr/message.php' );
		$this->assertEquals( 'TODO: child' , $lemmas[ 'lemma' ][ 'child' ] );
	}

	/**
	 * - Set a non existing lang folder
	 */
	public function testLangFolderDoesNotExist()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , self::LANG_DIR_PATH . 'doesnotexist' );

		$output = new BufferedOutput;

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
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , null );

		$output = new BufferedOutput;

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
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'lang_folder_path' , null );

		$output = new BufferedOutput;

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

		$output = new BufferedOutput;

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
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--output-flat'    => true ,
			'--new-value'      => '%LEMMA POTSKY' ,
		) , $output );

		$this->assertEquals( 0 , $return );

		/** @noinspection PhpIncludeInspection */
		$lemmas = include( self::LANG_DIR_PATH . '/fr/message.php' );
		$this->assertEquals( 'child POTSKY' , $lemmas[ 'lemma.child' ] );
	}


	/**
	 * - check a word is correctly translated
	 */
	public function testTranslations()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--output-flat'    => true ,
			'--translation'    => true ,
		) , $output );

		$this->assertEquals( 0 , $return );

		/** @noinspection PhpIncludeInspection */
		$lemmas = include( self::LANG_DIR_PATH . '/fr/message.php' );
		$this->assertEquals( 'TODO: chien' , $lemmas[ 'dog' ] );
		$this->assertEquals( 'TODO: chien' , $lemmas[ 'child.dog' ] );
	}


	/**
	 * - the default lang file array is structured
	 * - the new-value option works
	 */
	public function testVerbose()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--verbose'        => true ,
		) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'Lemmas will be searched in the following directories:' , $output->fetch() );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--verbose'        => true ,
		) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'Nothing to do for this file' , $output->fetch() );
	}


	/**
	 *
	 */
	public function testNothingToDo()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH_WO_LEMMA );

		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--verbose'        => true ,
		) , $output );

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'No lemma has been found in code.' , $output->fetch() );
	}


	/**
	 *
	 */
	public function testObsoleteLemma()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
		) , $output );

		$this->assertEquals( 0 , $return );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction'     => true ,
			'--verbose'            => true ,
			'--php-file-extension' => 'copy' ,
		) , $output );

		$this->assertEquals( 0 , $return );
	}


	/**
	 *
	 */
	public function testSilent()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--silent'         => true ,
		) , $output );

		// Exit code is 1 because there are new lemma to translate
		$this->assertEquals( 1 , $return );
		$this->assertEmpty( $output->fetch() );
	}


	/**
	 *
	 */
	public function testTranslationsNotConfigured()
	{
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'translators.Microsoft.client_id' , 'dumb' );
		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'translators.Microsoft.client_secret' , 'dumber' );

		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' );

		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		Artisan::call( 'localization:missing' , array(
			'--no-interaction' => true ,
			'--output-flat'    => true ,
			'--translation'    => true ,
		) , $output );

	}
}
