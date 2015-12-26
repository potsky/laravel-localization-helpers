<?php

class TestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

	protected function tearDown()
	{
		Mockery::close();
	}
}

