<?php

use Potsky\LaravelLocalizationHelpers\Factory\Translator;

class TranslatorTests extends TestCase
{
	/**
	 *
	 */
	public function testInjection()
	{
		$translator = new Translator( 'Microsoft' , array(
			'client_id'     => 'xxx' ,
			'client_secret' => 'yyy' ,
		) );
		$this->assertTrue( $translator instanceof \Potsky\LaravelLocalizationHelpers\Factory\Translator );
		$this->assertTrue( $translator->getTranslator() instanceof \Potsky\LaravelLocalizationHelpers\Factory\TranslatorMicrosoft );
	}

	/**
	 * Microsoft credentials are set in environment on my computer
	 * export LLH_MICROSOFT_TRANSLATOR_CLIENT_ID="..."
	 * export LLH_MICROSOFT_TRANSLATOR_CLIENT_SECRET="..."
	 *
	 * Go to your Azure account to retrieve client id and secret :
	 * https://datamarket.azure.com/developer/applications
	 */
	public function testRealCase()
	{
		$translator = new Translator( 'Microsoft' , array() );
		$this->assertEquals( 'Stuhl' , $translator->translate( 'chair' , 'de' ) );
		$this->assertEquals( 'Fleisch' , $translator->translate( 'chair' , 'de' , 'fr' ) );
	}

	/**
	 *
	 */
	public function testNoTranslation()
	{
		$translator = new Translator( 'Microsoft' , array() );
		$this->assertNull( $translator->translate( '' , '' ) );
	}

	/**
	 *
	 */
	public function testUnknownLang()
	{
		$translator = new Translator( 'Microsoft' , array() );
		$this->assertNull( $translator->translate( 'dog' , 'zz' ) );
	}

	/**
	 *
	 */
	public function testRealCaseWithDefaultLanguage()
	{
		$translator = new Translator( 'Microsoft' , array( 'default_language' => 'fr' ) );
		$this->assertEquals( 'Fleisch' , $translator->translate( 'chair' , 'de' ) );
	}

	/**
	 *
	 */
	public function testNoCredentialsClientId()
	{
		$this->setExpectedException( '\\Potsky\\LaravelLocalizationHelpers\\Factory\\Exception' , 'Please provide a client_id for Microsoft Bing Translator service' );
		new Translator( 'Microsoft' , array(
			'env_name_client_id'     => 'this_env_does_not_exist' ,
			'env_name_client_secret' => 'this_env_does_not_exist' ,
		) );
	}

	/**
	 *
	 */
	public function testNoCredentialsClientSecret()
	{
		$this->setExpectedException( '\\Potsky\\LaravelLocalizationHelpers\\Factory\\Exception' , 'Please provide a client_secret for Microsoft Bing Translator service' );
		new Translator( 'Microsoft' , array(
			'env_name_client_secret' => 'this_env_does_not_exist' ,
		) );
	}

}
