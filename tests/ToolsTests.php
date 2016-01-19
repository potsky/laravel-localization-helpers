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

		Tools::arraySetDotFirstLevel( $array , 'a.b.c' , true );
		$this->assertTrue( $array['a']['b.c'] );

		Tools::arraySetDotFirstLevel( $array , 'a.b.c' , true );
		$this->assertNull( @$array['a']['b']['c'] );

		Tools::arraySetDotFirstLevel( $array , null , true );
		/** @var bool $array */
		$this->assertTrue( $array );
	}
}
