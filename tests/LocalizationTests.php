<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;

class LocalizationTests extends TestCase
{
	/**
	 *
	 */
	public function testGetMessageBag()
	{
		$messageBag = new MessageBag();
		$manager    = new Localization( $messageBag );
		$this->assertSame( $messageBag , $manager->getMessageBag() );
	}

	/**
	 *
	 */
	public function testGetFilesWithExtensionWithNoDirPath()
	{
		$manager = new Localization( new MessageBag() );
		$this->assertCount( 0 , $manager->getFilesWithExtension( __FILE__ ) );
	}

	/**
	 *
	 */
	public function testNoTranslation()
	{
		$manager = new Localization( new MessageBag() );
		$this->assertEquals( 'AAA' , $manager->translate( 'AAA' , 'zz' ) );
	}

}
