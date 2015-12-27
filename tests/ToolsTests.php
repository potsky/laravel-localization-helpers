<?php

class ToolsTests extends TestCase
{
	public function testLaravelVersion()
	{
		$this->assertEquals( 4 , \Potsky\LaravelLocalizationHelpers\Tools::getLaravelMajorVersion() );
		$this->assertFalse( \Potsky\LaravelLocalizationHelpers\Tools::isLaravel5() );
	}

}
