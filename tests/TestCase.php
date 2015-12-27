<?php

class TestCase extends Orchestra\Testbench\TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
	}

	/**
	 * Get package providers.
	 *
	 * @return array
	 */
	protected function getPackageProviders()
	{
		return array( 'Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider' );
	}

	protected function tearDown()
	{
		Mockery::close();
	}
}

