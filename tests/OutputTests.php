<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;

class OutputTests extends TestCase
{
	/**
	 *
	 */
	public function test()
	{
		$messageBag = new MessageBag();
		$manager    = new Localization( $messageBag );

		print_r( $manager->fixCodeStyle( array( 'command' => 'fix' ) ) );

		$this->assertTrue( true );
	}

}
