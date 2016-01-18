<?php

use Potsky\LaravelLocalizationHelpers\Factory\Tools;

class ExceptionTests extends TestCase
{
	public function testParameters()
	{
		$e = new Potsky\LaravelLocalizationHelpers\Factory\Exception();
		$e->setParameter( 'coucou' );
		$this->assertEquals( 'coucou' , $e->getParameter() );
	}
}
