<?php

use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class ToolsTests extends TestCase
{
	public function testLaravelVersion()
	{
		$this->assertEquals( 5 , Tools::getLaravelMajorVersion() );
		$this->assertTrue( Tools::isLaravel5() );
	}

}
