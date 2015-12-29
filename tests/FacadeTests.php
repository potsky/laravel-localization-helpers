<?php

use Illuminate\Foundation\AliasLoader;

class FacadeTests extends TestCase
{
	public function testFacade()
	{
		$loader = AliasLoader::getInstance();
		$loader->alias( 'LocalizationHelpers', 'Potsky\LaravelLocalizationHelpers\Facade\LocalizationHelpers' );

		$this->assertTrue( LocalizationHelpers::test() );
	}
}
