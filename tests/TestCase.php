<?php

class TestCase extends Orchestra\Testbench\TestCase
{
	const MOCK_DIR_PATH           = 'tests/mock';
	const MOCK_DIR_PATH_GLOBAL    = 'tests/mock/global';
	const MOCK_DIR_PATH_WO_LEMMA  = 'tests/mock/wo_lemma';
	const LANG_DIR_PATH           = 'tests/lang';
	const ORCHESTRA_LANG_DIR_PATH = 'vendor/orchestra/testbench/fixture/resources/lang';

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
	 *
	 */
	public function tearDown()
	{
    }

	/**
	 * Get package providers.
	 *
	 * @return array
	 */
	protected function getPackageProviders( $app )
	{
		return array( 'Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider' );
	}
}

