<?php

class ConfigTests extends TestCase
{
	public function testLaravel4Config()
	{
		$config = include( 'src/config/config.php' );
		$this->assertInternalType( 'array' , $config );
	}

	public function testLaravel5Config()
	{
		$config = include( 'src/config/config-laravel5.php' );
		$this->assertInternalType( 'array' , $config );
	}

}
