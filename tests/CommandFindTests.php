<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandFindTests extends TestCase
{
	/**
	 * Setup the test environment.
	 *
	 * - Set custom configuration paths
	 */
	public function setUp()
	{
		parent::setUp();

		Config::set( Localization::PREFIX_LARAVEL_CONFIG . 'folders' , self::MOCK_DIR_PATH );
	}

	/**
	 *
	 */
	public function testSearchForRegularLemma()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:find' , array( 'lemma' => 'message.lemma' , '--verbose' => true , '--short' => true ) , $output );
		$result = $output->fetch();

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'Lemma message.lemma has been found in' , $result );
	}

	/**
	 *
	 */
	public function testSearchForRegexLemma()
	{
		$output = new BufferedOutput;

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:find' , array( 'lemma' => 'message\\.lemma.*' , '--verbose' => true , '--short' => true , '--regex' => true ) , $output );
		$result = $output->fetch();

		$this->assertEquals( 1 , $return );
		$this->assertContains( 'The argument is not a valid regular expression:' , $result );

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$return = Artisan::call( 'localization:find' , array( 'lemma' => '@message\\.lemma.*@' , '--verbose' => true , '--short' => true , '--regex' => true ) , $output );
		$result = $output->fetch();

		$this->assertEquals( 0 , $return );
		$this->assertContains( 'has been found in' , $result );

		$messageBag = new MessageBag();
		$manager    = new Localization( $messageBag );

		$trans_methods = array(
			'trans'        => array(
				'@trans\(\s*(\'.*\')\s*(,.*)*\)@U' ,
				'@trans\(\s*(".*")\s*(,.*)*\)@U' ,
			) ,
			'Lang::Get'    => array(
				'@Lang::Get\(\s*(\'.*\')\s*(,.*)*\)@U' ,
				'@Lang::Get\(\s*(".*")\s*(,.*)*\)@U' ,
				'@Lang::get\(\s*(\'.*\')\s*(,.*)*\)@U' ,
				'@Lang::get\(\s*(".*")\s*(,.*)*\)@U' ,
			) ,
			'trans_choice' => array(
				'@trans_choice\(\s*(\'.*\')\s*,.*\)@U' ,
				'@trans_choice\(\s*(".*")\s*,.*\)@U' ,
			) ,
			'Lang::choice' => array(
				'@Lang::choice\(\s*(\'.*\')\s*,.*\)@U' ,
				'@Lang::choice\(\s*(".*")\s*,.*\)@U' ,
			) ,
			'@lang'        => array(
				'@\@lang\(\s*(\'.*\')\s*(,.*)*\)@U' ,
				'@\@lang\(\s*(".*")\s*(,.*)*\)@U' ,
			) ,
			'@choice'      => array(
				'@\@choice\(\s*(\'.*\')\s*,.*\)@U' ,
				'@\@choice\(\s*(".*")\s*,.*\)@U' ,
			) ,
		);

		$return = $manager->findLemma( 'not a valid regex' , $manager->getPath( self::MOCK_DIR_PATH ) , $trans_methods , true , true );
		$messages = $messageBag->getMessages();
		$this->assertFalse( $return );
		$this->assertContains( 'The argument is not a valid regular expression:' , $messages[0][1] );
	}
}
