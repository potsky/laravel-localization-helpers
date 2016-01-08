<?php

class TestCase extends Orchestra\Testbench\TestCase
{
	const MOCK_DIR_PATH           = 'tests/mock';
	const MOCK_DIR_PATH_WO_LEMMA  = 'tests/mock/wo_lemma';
	const LANG_DIR_PATH           = 'tests/lang';
	const ORCHESTRA_LANG_DIR_PATH = 'vendor/orchestra/testbench/src/fixture/app/lang';

	/**
	 *
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	}

	/**
	 *
	 */
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

	/**
	 *
	 */
	protected function tearDown()
	{
		Mockery::close();
	}
}

