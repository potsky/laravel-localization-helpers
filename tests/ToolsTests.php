<?php

use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class ToolsTests extends TestCase
{
	public function testLaravelVersion()
	{
		$this->assertEquals( 5 , Tools::getLaravelMajorVersion() );
		$this->assertTrue( Tools::isLaravel5() );
	}

	public function testValidDirectory()
	{
		$this->assertFalse( Tools::isValidDirectory( __DIR__ , __FILE__ ) );
	}

	public function testArraySetDotFirstLevel()
	{
		$array = array();

		Tools::arraySet( $array , 'a.b.c' , true , '/\\./' , 2 );
		$this->assertTrue( $array['a']['b.c'] );

		Tools::arraySet( $array , 'a.b.c' , true , '/\\./' , 2 );
		$this->assertNull( @$array['a']['b']['c'] );

		Tools::arraySet( $array , null , true , '/\\./' , 2 );
		/** @var bool $array */
		$this->assertTrue( $array );
	}
}
