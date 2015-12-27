<?php

use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class ToolsTests extends TestCase
{
	public function testLaravelVersion()
	{
		$this->assertEquals( 4 , Tools::getLaravelMajorVersion() );
		$this->assertFalse( Tools::isLaravel5() );
	}

}
