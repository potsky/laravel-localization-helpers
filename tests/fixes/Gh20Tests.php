<?php

use Potsky\LaravelLocalizationHelpers\Factory\Localization;
use Potsky\LaravelLocalizationHelpers\Factory\MessageBag;

class Gh20Tests extends TestCase
{
	/**
	 * https://github.com/potsky/laravel-localization-helpers/issues/20
	 */
	public function testDotInPath()
	{
		$messageBag = new MessageBag();
		$manager    = new Localization( $messageBag );
		$now        = '20160129_202938';

		$this->assertSame( '/nia/nio/message.' . $now . '.php' , $manager->getBackupPath( '/nia/nio/message.php' , $now ) );
		$this->assertSame( '/var/www/kd/domain.com/message.' . $now . '.php' , $manager->getBackupPath( '/var/www/kd/domain.com/message.php' , $now ) );
		$this->assertSame( '/var/www/kd/domain.com/message.' . $now . '.txt' , $manager->getBackupPath( '/var/www/kd/domain.com/message.txt' , $now , 'txt' ) );
	}
}
